<?php

namespace Tests\Feature;

use App\Models\MediaProfile;
use App\Models\Submission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class JournalistTest extends TestCase
{
    use RefreshDatabase;

    public function test_media_can_create_journalist_account_for_its_company(): void
    {
        Storage::fake('public');
        $media = User::factory()->create(['role' => 'media']);
        Submission::create(['user_id' => $media->id, 'tahun_anggaran' => 2026, 'status' => 'disetujui']);
        $profile = MediaProfile::create(['user_id' => $media->id, 'nama_media' => 'Media Saya', 'jenis_media' => 'online', 'alamat' => 'Alamat', 'no_whatsapp' => '0812']);

        $response = $this->actingAs($media)->post(route('media.journalists.store'), [
            'name' => 'Jurnalis Satu', 'email' => 'jurnalis@example.com', 'password' => 'password', 'password_confirmation' => 'password',
            'phone' => '08123456789', 'position' => 'Reporter',
            'foto_wartawan' => UploadedFile::fake()->create('wartawan.jpg', 100, 'image/jpeg'),
            'sertifikat_kompetensi' => UploadedFile::fake()->create('sertifikat.pdf', 100, 'application/pdf'),
        ]);

        $response->assertRedirect(route('media.journalists.index'));
        $this->assertDatabaseHas('users', ['email' => 'jurnalis@example.com', 'role' => 'jurnalis']);
        $journalist = $profile->journalists()->firstOrFail();
        $this->assertSame('Reporter', $journalist->position);
        $this->assertStringStartsWith('journalists/'.$profile->id.'/', $journalist->foto_wartawan_path);
        $this->assertStringStartsWith('journalists/'.$profile->id.'/', $journalist->sertifikat_kompetensi_path);
        Storage::disk('public')->assertExists($journalist->foto_wartawan_path);
        Storage::disk('public')->assertExists($journalist->sertifikat_kompetensi_path);
    }

    public function test_media_cannot_assign_journalist_to_another_company(): void
    {
        Storage::fake('public');
        $media = User::factory()->create(['role' => 'media']);
        Submission::create(['user_id' => $media->id, 'tahun_anggaran' => 2026, 'status' => 'disetujui']);
        $other = User::factory()->create(['role' => 'media']);
        $profile = MediaProfile::create(['user_id' => $media->id, 'nama_media' => 'Media Saya', 'jenis_media' => 'online', 'alamat' => 'Alamat', 'no_whatsapp' => '0812']);
        $otherProfile = MediaProfile::create(['user_id' => $other->id, 'nama_media' => 'Media Lain', 'jenis_media' => 'cetak', 'alamat' => 'Alamat', 'no_whatsapp' => '0813']);

        $response = $this->actingAs($media)->post(route('media.journalists.store'), [
            'media_profile_id' => $otherProfile->id, 'name' => 'Jurnalis Dua', 'email' => 'jurnalis2@example.com', 'password' => 'password', 'password_confirmation' => 'password',
            'foto_wartawan' => UploadedFile::fake()->create('wartawan.jpg', 100, 'image/jpeg'),
            'sertifikat_kompetensi' => UploadedFile::fake()->create('sertifikat.pdf', 100, 'application/pdf'),
        ]);

        $response->assertRedirect(route('media.journalists.index'));
        $this->assertDatabaseHas('journalists', ['media_profile_id' => $profile->id]);
        $this->assertDatabaseMissing('journalists', ['media_profile_id' => $otherProfile->id]);
        $this->assertDatabaseHas('users', ['email' => 'jurnalis2@example.com', 'role' => 'jurnalis']);
    }

    public function test_media_without_profile_sees_empty_journalist_list_instead_of_server_error(): void
    {
        $media = User::factory()->create(['role' => 'media']);
        Submission::create(['user_id' => $media->id, 'tahun_anggaran' => 2026, 'status' => 'disetujui']);

        $this->actingAs($media)->get(route('media.journalists.index'))
            ->assertOk()
            ->assertSee('Belum ada jurnalis.');
    }

    public function test_admin_cannot_open_or_submit_journalist_creation_form(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->get('/admin/jurnalis/buat')->assertNotFound();
        $this->actingAs($admin)->post('/admin/jurnalis')->assertNotFound();
    }

    public function test_media_without_profile_gets_validation_error_when_creating_journalist(): void
    {
        $media = User::factory()->create(['role' => 'media']);
        Submission::create(['user_id' => $media->id, 'tahun_anggaran' => 2026, 'status' => 'disetujui']);

        $this->actingAs($media)->from(route('media.journalists.create'))->post(route('media.journalists.store'), [
            'name' => 'Jurnalis Baru',
            'email' => 'jurnalis-baru@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'foto_wartawan' => UploadedFile::fake()->create('wartawan.jpg', 100, 'image/jpeg'),
            'sertifikat_kompetensi' => UploadedFile::fake()->create('sertifikat.pdf', 100, 'application/pdf'),
        ])->assertRedirect(route('media.journalists.create'))->assertSessionHasErrors('media_profile_id');

        $this->assertDatabaseMissing('users', ['email' => 'jurnalis-baru@example.com']);
    }
}
