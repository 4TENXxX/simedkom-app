<?php

namespace Tests\Feature;

use App\Models\Contract;
use App\Models\MediaProfile;
use App\Models\Submission;
use App\Models\NewsClipping;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class InvoiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_media_can_submit_invoice_matching_valid_publications(): void
    {
        Storage::fake('private');
        $media = User::factory()->create(['role' => 'media']);
        Submission::create(['user_id' => $media->id, 'tahun_anggaran' => 2026, 'status' => 'disetujui']);
        $profile = MediaProfile::create(['user_id' => $media->id, 'nama_media' => 'Media', 'jenis_media' => 'online', 'alamat' => 'Alamat', 'no_whatsapp' => '0812']);
        $contract = Contract::create(['media_profile_id' => $profile->id, 'nomor_kontrak' => 'INV/KONTRAK', 'tanggal_mulai' => '2026-01-01', 'tanggal_berakhir' => '2026-12-31', 'nilai_kontrak' => 1000, 'kuota_berita' => 2, 'harga_per_publikasi' => 500, 'jenis_layanan' => 'Online', 'status' => 'aktif']);
        NewsClipping::create(['contract_id' => $contract->id, 'media_profile_id' => $profile->id, 'submitted_by' => $media->id, 'type' => 'online', 'url' => 'https://example.test/valid', 'title' => 'Valid', 'published_at' => '2026-09-01', 'status' => 'valid']);

        $response = $this->actingAs($media)->post(route('media.invoices.store'), [
            'contract_id' => $contract->id, 'invoice_number' => 'INV/001', 'invoice_date' => '2026-09-23', 'period_start' => '2026-09-01', 'period_end' => '2026-09-30',
            'subtotal' => 500, 'tax' => 50, 'total' => 550, 'invoice' => UploadedFile::fake()->create('invoice.pdf', 100, 'application/pdf'),
        ]);

        $response->assertRedirect(route('media.invoices.index'));
        $this->assertDatabaseHas('invoices', ['invoice_number' => 'INV/001', 'subtotal' => 500, 'status' => 'pending_finance_review']);
    }

    public function test_invoice_with_wrong_subtotal_is_rejected(): void
    {
        Storage::fake('private');
        $media = User::factory()->create(['role' => 'media']);
        Submission::create(['user_id' => $media->id, 'tahun_anggaran' => 2026, 'status' => 'disetujui']);
        $profile = MediaProfile::create(['user_id' => $media->id, 'nama_media' => 'Media', 'jenis_media' => 'online', 'alamat' => 'Alamat', 'no_whatsapp' => '0812']);
        $contract = Contract::create(['media_profile_id' => $profile->id, 'nomor_kontrak' => 'INV/KONTRAK/2', 'tanggal_mulai' => '2026-01-01', 'tanggal_berakhir' => '2026-12-31', 'nilai_kontrak' => 1000, 'kuota_berita' => 2, 'harga_per_publikasi' => 500, 'jenis_layanan' => 'Online', 'status' => 'aktif']);

        $response = $this->actingAs($media)->from(route('media.invoices.create'))->post(route('media.invoices.store'), [
            'contract_id' => $contract->id, 'invoice_number' => 'INV/002', 'invoice_date' => '2026-09-23', 'period_start' => '2026-09-01', 'period_end' => '2026-09-30',
            'subtotal' => 500, 'tax' => 50, 'total' => 550, 'invoice' => UploadedFile::fake()->create('invoice.pdf', 100, 'application/pdf'),
        ]);

        $response->assertRedirect(route('media.invoices.create'))->assertSessionHasErrors('subtotal');
        $this->assertDatabaseMissing('invoices', ['invoice_number' => 'INV/002']);
    }

    public function test_media_invoice_form_renders_without_blade_parse_error(): void
    {
        $media = User::factory()->create(['role' => 'media']);
        Submission::create(['user_id' => $media->id, 'tahun_anggaran' => 2026, 'status' => 'disetujui']);

        $this->actingAs($media)->get(route('media.invoices.create'))
            ->assertOk()
            ->assertSee('Ajukan invoice');
    }
}
