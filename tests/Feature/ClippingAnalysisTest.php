<?php

namespace Tests\Feature;

use App\Models\Contract;
use App\Models\MediaProfile;
use App\Models\NewsClipping;
use App\Models\SentimentResult;
use App\Models\SystemSetting;
use App\Models\Submission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ClippingAnalysisTest extends TestCase
{
    use RefreshDatabase;

    public function test_online_clipping_stores_scraping_keywords_and_sentiment_results(): void
    {
        Http::fake([
            'https://news.example.test/*' => Http::response('<html><head><title>Program berhasil dan positif</title><link rel="canonical" href="https://news.example.test/canonical"></head><body>Pemerintah sukses menjalankan program wilayah.</body></html>', 200),
        ]);
        SystemSetting::create(['key' => 'institution_keywords', 'value' => ['pemerintah', 'program']]);
        [$media, $profile, $contract] = $this->createContract();

        $this->actingAs($media)->post(route('media.clippings.store'), [
            'contract_id' => $contract->id, 'type' => 'online', 'url' => 'https://news.example.test/article',
            'title' => 'Judul pengajuan', 'published_at' => '2026-09-20',
        ])->assertRedirect(route('media.clippings.index'));

        $clipping = NewsClipping::firstOrFail();
        $this->assertDatabaseHas('scraping_results', ['news_clipping_id' => $clipping->id, 'status' => 'success']);
        $this->assertDatabaseHas('keyword_matches', ['news_clipping_id' => $clipping->id, 'keyword' => 'pemerintah', 'match_count' => 1]);
        $this->assertSame('positif', SentimentResult::firstOrFail()->label);
        $this->assertSame('found', $clipping->fresh()->keyword_status);
    }

    public function test_scraping_failure_does_not_delete_clipping_and_marks_manual_review(): void
    {
        Http::fake(['https://news.example.test/*' => Http::failedConnection()]);
        [$media, , $contract] = $this->createContract();

        $this->actingAs($media)->post(route('media.clippings.store'), [
            'contract_id' => $contract->id, 'type' => 'online', 'url' => 'https://news.example.test/unavailable',
            'title' => 'Judul pengajuan', 'published_at' => '2026-09-20',
        ])->assertRedirect(route('media.clippings.index'));

        $this->assertDatabaseHas('scraping_results', ['status' => 'failed']);
        $this->assertDatabaseHas('news_clippings', ['keyword_status' => 'manual_review']);
    }

    /** @return array{0: User, 1: MediaProfile, 2: Contract} */
    private function createContract(): array
    {
        $media = User::factory()->create(['role' => 'media']);
        Submission::create(['user_id' => $media->id, 'tahun_anggaran' => 2026, 'status' => 'disetujui']);
        $profile = MediaProfile::create(['user_id' => $media->id, 'nama_media' => 'Media', 'jenis_media' => 'online', 'alamat' => 'Alamat', 'no_whatsapp' => '0812']);
        $contract = Contract::create(['media_profile_id' => $profile->id, 'nomor_kontrak' => 'ANALISIS/'.uniqid(), 'tanggal_mulai' => '2026-01-01', 'tanggal_berakhir' => '2026-12-31', 'nilai_kontrak' => 1000, 'kuota_berita' => 10, 'harga_per_publikasi' => 100, 'jenis_layanan' => 'Online', 'status' => 'aktif']);

        return [$media, $profile, $contract];
    }
}
