<?php

namespace Tests\Feature;

use App\Models\AppNotification;
use App\Models\PressRelease;
use App\Models\Submission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PressReleaseTest extends TestCase
{
    use RefreshDatabase;

    public function test_publishing_press_release_creates_notifications_for_media_partners(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $media = User::factory()->create(['role' => 'media']);
        Submission::create(['user_id' => $media->id, 'tahun_anggaran' => 2026, 'status' => 'disetujui']);
        $otherMedia = User::factory()->create(['role' => 'media']);
        $journalist = User::factory()->create(['role' => 'jurnalis']);
        $pressRelease = PressRelease::create([
            'author_id' => $admin->id,
            'title' => 'Informasi program pemerintah',
            'summary' => 'Ringkasan informasi',
            'body' => 'Isi siaran pers.',
            'status' => 'draft',
        ]);

        $response = $this->actingAs($admin)->patch(route('admin.press-releases.publish', $pressRelease));

        $response->assertRedirect();
        $this->assertSame(3, AppNotification::where('press_release_id', $pressRelease->id)->count());
        $this->assertDatabaseHas('notifications', ['user_id' => $media->id, 'press_release_id' => $pressRelease->id]);
        $this->assertDatabaseHas('notifications', ['user_id' => $otherMedia->id, 'press_release_id' => $pressRelease->id]);
        $this->assertDatabaseHas('notifications', ['user_id' => $journalist->id, 'press_release_id' => $pressRelease->id]);
    }

    public function test_media_feed_only_shows_published_press_releases(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $media = User::factory()->create(['role' => 'media']);
        Submission::create(['user_id' => $media->id, 'tahun_anggaran' => 2026, 'status' => 'disetujui']);
        PressRelease::create([
            'author_id' => $admin->id,
            'title' => 'Informasi terbit',
            'body' => 'Isi',
            'status' => 'dipublikasikan',
            'published_at' => now(),
        ]);
        PressRelease::create([
            'author_id' => $admin->id,
            'title' => 'Informasi draft',
            'body' => 'Isi',
            'status' => 'draft',
        ]);

        $this->actingAs($media)->get(route('media.press-releases.index'))
            ->assertOk()
            ->assertSee('Informasi terbit')
            ->assertDontSee('Informasi draft');
    }

    public function test_admin_press_release_form_renders_without_blade_parse_error(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->get(route('admin.press-releases.create'))
            ->assertOk()
            ->assertSee('Buat siaran pers');
    }
}
