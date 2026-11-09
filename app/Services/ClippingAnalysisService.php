<?php

namespace App\Services;

use App\Models\KeywordMatch;
use App\Models\NewsClipping;
use App\Models\ScrapingResult;
use App\Models\SentimentResult;
use App\Models\SystemSetting;
use Illuminate\Support\Facades\Http;
use Throwable;

class ClippingAnalysisService
{
    public function analyze(NewsClipping $clipping): void
    {
        if ($clipping->type !== 'online' || ! $clipping->url) {
            return;
        }

        try {
            $response = Http::timeout(8)
                ->connectTimeout(3)
                ->withHeaders(['User-Agent' => 'SIMEDKOM/1.0 (+news-validation)'])
                ->get($clipping->url);

            $html = mb_substr($response->body(), 0, 2_000_000);
            [$title, $content, $canonicalUrl] = $this->extractMetadata($html, $clipping->url);

            ScrapingResult::updateOrCreate(['news_clipping_id' => $clipping->id], [
                'status' => $response->successful() ? 'success' : 'http_error',
                'canonical_url' => $canonicalUrl,
                'scraped_title' => $title,
                'scraped_content' => $content,
                'http_status' => $response->status(),
                'error_message' => $response->successful() ? null : 'HTTP response tidak berhasil.',
                'scraped_at' => now(),
            ]);

            $this->storeKeywords($clipping, $title.' '.$content);
            $this->storeSentiment($clipping, $title.' '.$content);
        } catch (Throwable $exception) {
            ScrapingResult::updateOrCreate(['news_clipping_id' => $clipping->id], [
                'status' => 'failed',
                'error_message' => $exception->getMessage(),
                'scraped_at' => now(),
            ]);
            $clipping->update(['keyword_status' => 'manual_review']);
        }
    }

    /** @return array{0: string, 1: string, 2: string|null} */
    private function extractMetadata(string $html, string $fallbackUrl): array
    {
        $document = new \DOMDocument;
        @$document->loadHTML($html);
        $xpath = new \DOMXPath($document);
        $title = trim($xpath->evaluate('string(//meta[@property="og:title"]/@content)'))
            ?: trim($xpath->evaluate('string(//title)'));
        $content = trim(preg_replace('/\s+/', ' ', $xpath->evaluate('string(//body)')) ?? '');
        $canonical = trim($xpath->evaluate('string(//link[@rel="canonical"]/@href)')) ?: $fallbackUrl;

        return [$title, mb_substr($content, 0, 1_000_000), $canonical];
    }

    private function storeKeywords(NewsClipping $clipping, string $text): void
    {
        $keywords = SystemSetting::where('key', 'institution_keywords')->value('value') ?? [];
        $clipping->keywordMatches()->delete();
        $found = false;

        foreach ($keywords as $keyword) {
            $count = substr_count(mb_strtolower($text), mb_strtolower((string) $keyword));
            if ($count > 0) {
                $found = true;
                KeywordMatch::create(['news_clipping_id' => $clipping->id, 'keyword' => $keyword, 'match_count' => $count]);
            }
        }

        $clipping->update(['keyword_status' => $keywords === [] ? 'manual_review' : ($found ? 'found' : 'not_found')]);
    }

    private function storeSentiment(NewsClipping $clipping, string $text): void
    {
        $positive = ['baik', 'sukses', 'berhasil', 'positif', 'apresiasi', 'meningkat'];
        $negative = ['buruk', 'gagal', 'negatif', 'kritik', 'menurun', 'masalah'];
        $lowerText = mb_strtolower($text);
        $positiveCount = $this->countWords($lowerText, $positive);
        $negativeCount = $this->countWords($lowerText, $negative);
        $label = $positiveCount === $negativeCount ? 'netral' : ($positiveCount > $negativeCount ? 'positif' : 'negatif');
        $total = $positiveCount + $negativeCount;

        SentimentResult::updateOrCreate(['news_clipping_id' => $clipping->id], [
            'label' => $label,
            'confidence' => $total > 0 ? round((max($positiveCount, $negativeCount) / $total) * 100, 2) : null,
            'engine_version' => 'lexicon-id-1.0',
            'analyzed_text' => mb_substr($text, 0, 1_000_000),
            'analyzed_at' => now(),
        ]);
        $clipping->update(['sentiment_label' => $label, 'sentiment_confidence' => $total > 0 ? round((max($positiveCount, $negativeCount) / $total) * 100, 2) : null]);
    }

    /** @param array<int, string> $words */
    private function countWords(string $text, array $words): int
    {
        return array_sum(array_map(fn (string $word): int => substr_count($text, $word), $words));
    }
}
