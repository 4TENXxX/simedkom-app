<?php

namespace App\Http\Controllers;

use App\Models\Contract;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    public function clippings(Request $request): StreamedResponse
    {
        $contractId = $request->integer('contract_id');
        $contracts = Contract::with('mediaProfile')->when($contractId, fn ($query) => $query->whereKey($contractId))->get();

        return response()->streamDownload(function () use ($contracts): void {
            $handle = fopen('php://output', 'wb');
            fputcsv($handle, ['Media', 'Nomor Kontrak', 'Total Diajukan', 'Total Valid', 'Total Ditolak', 'Sentimen Positif', 'Sentimen Netral', 'Sentimen Negatif', 'Kuota', 'Terpakai', 'Tersisa', 'Capaian (%)', 'Nilai Publikasi']);

            foreach ($contracts as $contract) {
                $clippings = $contract->newsClippings()->get();
                $valid = $clippings->where('status', 'valid');
                fputcsv($handle, [
                    $contract->mediaProfile->nama_media,
                    $contract->nomor_kontrak,
                    $clippings->count(),
                    $valid->count(),
                    $clippings->where('status', 'ditolak')->count(),
                    $valid->where('sentiment_label', 'positif')->count(),
                    $valid->where('sentiment_label', 'netral')->count(),
                    $valid->where('sentiment_label', 'negatif')->count(),
                    $contract->kuota_berita,
                    $valid->count(),
                    max(0, $contract->kuota_berita - $valid->count()),
                    $contract->persentase_capaian,
                    $valid->count() * (float) $contract->harga_per_publikasi,
                ]);
            }

            fclose($handle);
        }, 'laporan-kinerja-'.now()->format('YmdHis').'.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }
}
