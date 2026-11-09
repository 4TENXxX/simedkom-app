<?php

namespace Tests\Feature;

use App\Models\Submission;
use App\Models\Document;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MediaSubmissionWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_media_cannot_submit_a_second_company_application(): void
    {
        Storage::fake('public');
        $media = User::factory()->create(['role' => 'media']);
        $submission = Submission::create([
            'user_id' => $media->id,
            'tahun_anggaran' => 2026,
            'status' => 'pending',
        ]);

        $response = $this->actingAs($media)->post(route('media.submissions.store'), $this->submissionData());

        $response->assertRedirect(route('media.dashboard'));
        $response->assertSessionHas('error');
        $this->assertSame(1, Submission::where('user_id', $media->id)->count());
        $this->assertModelExists($submission);
    }

    public function test_company_requirements_exclude_journalist_specific_documents(): void
    {
        $companyDocumentTypes = array_keys(Document::types());

        $this->assertCount(12, $companyDocumentTypes);
        $this->assertNotContains('PasFoto', $companyDocumentTypes);
        $this->assertNotContains('SertifikatWartawan', $companyDocumentTypes);
        $this->assertContains('SuratPenunjukan', $companyDocumentTypes);
        $this->assertContains('SuratKuasa', $companyDocumentTypes);
        $this->assertContains('FotoKantor', $companyDocumentTypes);
    }

    public function test_rejected_application_cannot_be_submitted_again(): void
    {
        $media = User::factory()->create(['role' => 'media']);
        Submission::create([
            'user_id' => $media->id,
            'tahun_anggaran' => 2026,
            'status' => 'ditolak',
        ]);

        $response = $this->actingAs($media)->get(route('media.submissions.create'));

        $response->assertRedirect(route('media.dashboard'));
        $response->assertSessionHas('error');
    }

    public function test_media_can_replace_documents_when_application_needs_revision(): void
    {
        Storage::fake('public');
        $media = User::factory()->create(['role' => 'media']);
        $submission = Submission::create([
            'user_id' => $media->id,
            'tahun_anggaran' => 2026,
            'status' => 'perbaikan',
            'catatan_admin' => 'Proposal perlu diperbarui.',
        ]);
        $oldProposal = $submission->documents()->create([
            'jenis_dokumen' => 'Proposal',
            'file_path' => 'submissions/1/old-proposal.pdf',
        ]);
        Storage::disk('public')->put($oldProposal->file_path, 'old');

        $response = $this->actingAs($media)->patch(
            route('media.submissions.update', $submission),
            [
                'nama_media' => 'Media Nusantara',
                'jenis_media' => 'online',
                'alamat' => 'Jl. Merdeka No. 1',
                'no_whatsapp' => '08123456789',
                'tahun_anggaran' => 2026,
                'documents' => [
                    'Proposal' => UploadedFile::fake()->create('proposal-baru.pdf', 100, 'application/pdf'),
                ],
            ]
        );

        $response->assertRedirect(route('media.dashboard'));
        $this->assertDatabaseHas('submissions', [
            'id' => $submission->id,
            'status' => 'pending',
            'catatan_admin' => null,
        ]);
        $this->assertDatabaseHas('submission_status_histories', [
            'submission_id' => $submission->id,
            'old_status' => 'perbaikan',
            'new_status' => 'pending',
        ]);
        $this->assertTrue(Storage::disk('public')->missing($oldProposal->file_path));
        $newPath = $submission->fresh()->documents()->firstOrFail()->file_path;
        $this->assertNotSame($oldProposal->file_path, $newPath);
        $this->assertTrue(Storage::disk('public')->exists($newPath));
    }

    /**
     * @return array<string, mixed>
     */
    private function submissionData(): array
    {
        return [
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
        ];
    }
}
