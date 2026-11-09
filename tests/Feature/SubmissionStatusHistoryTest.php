<?php

namespace Tests\Feature;

use App\Models\Submission;
use App\Models\SubmissionStatusHistory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SubmissionStatusHistoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_media_submission_creates_initial_status_history(): void
    {
        Storage::fake('public');
        $media = User::factory()->create(['role' => 'media']);

        $response = $this->actingAs($media)->post(route('media.submissions.store'), [
            'nama_media' => 'Media Nusantara',
            'jenis_media' => 'online',
            'alamat' => 'Jl. Merdeka No. 1',
            'no_whatsapp' => '08123456789',
            'tahun_anggaran' => 2026,
            'documents' => [
                'SuratPermohonan' => UploadedFile::fake()->create('surat-permohonan.pdf', 100, 'application/pdf'),
                'RAB' => UploadedFile::fake()->create('rab.pdf', 100, 'application/pdf'),
                'AktaPendirian' => UploadedFile::fake()->create('akta-pendirian.pdf', 100, 'application/pdf'),
                'IzinUsaha' => UploadedFile::fake()->create('izin-usaha.pdf', 100, 'application/pdf'),
                'NIB' => UploadedFile::fake()->create('nib.pdf', 100, 'application/pdf'),
                'NPWP' => UploadedFile::fake()->create('npwp.pdf', 100, 'application/pdf'),
                'SPT' => UploadedFile::fake()->create('spt.pdf', 100, 'application/pdf'),
                'VerifikasiDewanPers' => UploadedFile::fake()->create('verifikasi-dewan-pers.pdf', 100, 'application/pdf'),
                'SuratPenunjukan' => UploadedFile::fake()->create('surat-penunjukan.pdf', 100, 'application/pdf'),
                'SuratKuasa' => UploadedFile::fake()->create('surat-kuasa.pdf', 100, 'application/pdf'),
                'Rekening' => UploadedFile::fake()->create('rekening.pdf', 100, 'application/pdf'),
                'FotoKantor' => UploadedFile::fake()->create('foto-kantor.jpg', 100, 'image/jpeg'),
            ],
        ]);

        $submission = Submission::firstOrFail();

        $response->assertRedirect(route('media.dashboard'));
        $this->assertDatabaseHas('submission_status_histories', [
            'submission_id' => $submission->id,
            'user_id' => $media->id,
            'old_status' => null,
            'new_status' => 'pending',
        ]);
    }

    public function test_admin_status_change_creates_status_history_with_note(): void
    {
        $media = User::factory()->create(['role' => 'media']);
        $admin = User::factory()->create(['role' => 'admin']);
        $submission = Submission::create([
            'user_id' => $media->id,
            'status' => 'pending',
            'tahun_anggaran' => 2026,
        ]);

        $response = $this->actingAs($admin)->patch(route('admin.submissions.update', $submission), [
            'status' => 'ditolak',
            'catatan_admin' => 'Dokumen belum lengkap.',
        ]);

        $response->assertRedirect(route('admin.dashboard'));
        $this->assertDatabaseHas('submission_status_histories', [
            'submission_id' => $submission->id,
            'user_id' => $admin->id,
            'old_status' => 'pending',
            'new_status' => 'ditolak',
            'catatan_admin' => 'Dokumen belum lengkap.',
        ]);
        $this->assertSame(1, SubmissionStatusHistory::where('submission_id', $submission->id)->count());
    }
}
