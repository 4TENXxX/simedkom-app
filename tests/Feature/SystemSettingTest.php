<?php

namespace Tests\Feature;

use App\Models\SystemSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SystemSettingTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_save_institution_keywords(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->put(route('admin.settings.keywords.update'), [
            'keywords' => ["Pemerintah\nProgram unggulan\nPemerintah"],
        ]);

        $response->assertRedirect();
        $this->assertSame(['Pemerintah', 'Program unggulan'], SystemSetting::firstOrFail()->value);
    }

    public function test_media_cannot_change_institution_keywords(): void
    {
        $media = User::factory()->create(['role' => 'media']);

        $this->actingAs($media)->put(route('admin.settings.keywords.update'), [
            'keywords' => ['Rahasia'],
        ])->assertForbidden();
    }
}
