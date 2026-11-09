<?php

namespace Tests\Feature;

use App\Models\Contract;
use App\Models\MediaProfile;
use App\Models\Submission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ContractTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_contract_for_media_partner(): void
    {
        Storage::fake('private');
        $admin = User::factory()->create(['role' => 'admin']);
        $media = User::factory()->create(['role' => 'media']);
        $profile = MediaProfile::create([
            'user_id' => $media->id,
            'nama_media' => 'Media Nusantara',
            'jenis_media' => 'online',
            'alamat' => 'Jl. Merdeka No. 1',
            'no_whatsapp' => '08123456789',
        ]);

        $response = $this->actingAs($admin)->post(route('admin.contracts.store'), [
            'media_profile_id' => $profile->id,
            'nomor_kontrak' => 'KONTRAK/001/2026',
            'tanggal_mulai' => '2026-01-01',
            'tanggal_berakhir' => '2026-12-31',
            'nilai_kontrak' => 12000000,
            'kuota_berita' => 24,
            'harga_per_publikasi' => 500000,
            'jenis_layanan' => 'Publikasi berita online',
            'status' => 'aktif',
            'dokumen_kontrak' => UploadedFile::fake()->create('kontrak.pdf', 100, 'application/pdf'),
        ]);

        $response->assertRedirect(route('admin.contracts.index'));
        $this->assertDatabaseHas('contracts', [
            'media_profile_id' => $profile->id,
            'nomor_kontrak' => 'KONTRAK/001/2026',
            'status' => 'aktif',
            'kuota_berita' => 24,
        ]);
    }

    public function test_media_can_only_see_its_own_contracts(): void
    {
        $media = User::factory()->create(['role' => 'media']);
        Submission::create(['user_id' => $media->id, 'tahun_anggaran' => 2026, 'status' => 'disetujui']);
        $otherMedia = User::factory()->create(['role' => 'media']);
        $profile = MediaProfile::create([
            'user_id' => $media->id,
            'nama_media' => 'Media Saya',
            'jenis_media' => 'online',
            'alamat' => 'Alamat',
            'no_whatsapp' => '08123456789',
        ]);
        $otherProfile = MediaProfile::create([
            'user_id' => $otherMedia->id,
            'nama_media' => 'Media Lain',
            'jenis_media' => 'cetak',
            'alamat' => 'Alamat lain',
            'no_whatsapp' => '08123456780',
        ]);
        Contract::create([
            'media_profile_id' => $profile->id,
            'nomor_kontrak' => 'KONTRAK/SAYA',
            'tanggal_mulai' => '2026-01-01',
            'tanggal_berakhir' => '2026-12-31',
            'nilai_kontrak' => 100,
            'kuota_berita' => 1,
            'harga_per_publikasi' => 100,
            'jenis_layanan' => 'Online',
            'status' => 'aktif',
        ]);
        Contract::create([
            'media_profile_id' => $otherProfile->id,
            'nomor_kontrak' => 'KONTRAK/LAIN',
            'tanggal_mulai' => '2026-01-01',
            'tanggal_berakhir' => '2026-12-31',
            'nilai_kontrak' => 100,
            'kuota_berita' => 1,
            'harga_per_publikasi' => 100,
            'jenis_layanan' => 'Cetak',
            'status' => 'aktif',
        ]);

        $response = $this->actingAs($media)->get(route('media.contracts.index'));

        $response->assertOk()->assertSee('KONTRAK/SAYA')->assertDontSee('KONTRAK/LAIN');
    }

    public function test_media_without_profile_can_open_contract_page(): void
    {
        $media = User::factory()->create(['role' => 'media']);
        Submission::create(['user_id' => $media->id, 'tahun_anggaran' => 2026, 'status' => 'disetujui']);

        $this->actingAs($media)->get(route('media.contracts.index'))
            ->assertOk()
            ->assertSee('Belum ada kontrak.');
    }
}
