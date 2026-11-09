<?php

namespace Tests\Feature;

use App\Models\Contract;
use App\Models\MediaProfile;
use App\Models\NewsClipping;
use App\Models\Submission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NewsClippingTest extends TestCase
{
    use RefreshDatabase;

    public function test_media_can_submit_clipping_and_duplicate_url_is_flagged(): void
    {
        [$media, $profile, $contract] = $this->createMediaWithContract();

        $payload = [
            'contract_id' => $contract->id,
            'type' => 'online',
            'url' => 'https://news.example.test/berita-1',
            'title' => 'Berita pertama',
            'published_at' => '2026-09-20',
        ];

        $this->actingAs($media)->post(route('media.clippings.store'), $payload)->assertRedirect(route('media.clippings.index'));
        $this->actingAs($media)->post(route('media.clippings.store'), $payload)->assertRedirect(route('media.clippings.index'));

        $this->assertDatabaseHas('news_clippings', ['url' => $payload['url'], 'duplicate_status' => 'duplicate_url']);
    }

    public function test_admin_approval_counts_only_valid_clippings_toward_contract_quota(): void
    {
        [$media, , $contract] = $this->createMediaWithContract(1);
        $admin = User::factory()->create(['role' => 'admin']);
        $clipping = NewsClipping::create([
            'contract_id' => $contract->id,
            'media_profile_id' => $media->mediaProfile->id,
            'submitted_by' => $media->id,
            'type' => 'online',
            'url' => 'https://news.example.test/approval',
            'title' => 'Berita approval',
            'published_at' => '2026-09-20',
            'status' => 'diajukan',
        ]);

        $this->actingAs($admin)->patch(route('admin.clippings.update', $clipping), [
            'status' => 'valid',
            'admin_notes' => 'Memenuhi ketentuan.',
        ])->assertRedirect(route('admin.clippings.index'));

        $this->assertSame(1, $contract->fresh()->kuota_terpakai);
        $this->assertSame(0, $contract->fresh()->kuota_tersisa);

        $second = NewsClipping::create([
            'contract_id' => $contract->id,
            'media_profile_id' => $media->mediaProfile->id,
            'submitted_by' => $media->id,
            'type' => 'online',
            'url' => 'https://news.example.test/second',
            'title' => 'Berita kedua',
            'published_at' => '2026-09-20',
            'status' => 'diajukan',
        ]);

        $this->actingAs($admin)->patch(route('admin.clippings.update', $second), [
            'status' => 'valid',
        ])->assertSessionHasErrors('status');
        $this->assertSame('diajukan', $second->fresh()->status);
    }

    public function test_media_clipping_form_renders_without_blade_parse_error(): void
    {
        [$media] = $this->createMediaWithContract();

        $this->actingAs($media)->get(route('media.clippings.create'))
            ->assertOk()
            ->assertSee('Ajukan bukti tayang');
    }

    /** @return array{0: User, 1: MediaProfile, 2: Contract} */
    private function createMediaWithContract(int $quota = 10): array
    {
        $media = User::factory()->create(['role' => 'media']);
        Submission::create(['user_id' => $media->id, 'tahun_anggaran' => 2026, 'status' => 'disetujui']);
        $profile = MediaProfile::create([
            'user_id' => $media->id,
            'nama_media' => 'Media Nusantara',
            'jenis_media' => 'online',
            'alamat' => 'Alamat',
            'no_whatsapp' => '08123456789',
        ]);
        $contract = Contract::create([
            'media_profile_id' => $profile->id,
            'nomor_kontrak' => 'KONTRAK/'.uniqid(),
            'tanggal_mulai' => '2026-01-01',
            'tanggal_berakhir' => '2026-12-31',
            'nilai_kontrak' => 1000,
            'kuota_berita' => $quota,
            'harga_per_publikasi' => 100,
            'jenis_layanan' => 'Online',
            'status' => 'aktif',
        ]);

        return [$media, $profile, $contract];
    }
}
