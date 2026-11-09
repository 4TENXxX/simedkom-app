<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_api_login_returns_jwt_and_me_requires_bearer_token(): void
    {
        $user = User::factory()->create(['email' => 'api@example.com', 'password' => 'password', 'role' => 'media']);
        $response = $this->postJson('/api/auth/login', ['email' => $user->email, 'password' => 'password']);

        $response->assertOk()->assertJsonPath('success', true)->assertJsonStructure(['data' => ['token', 'user']]);
        $token = $response->json('data.token');
        $this->getJson('/api/me')->assertUnauthorized();
        $this->withHeader('Authorization', 'Bearer '.$token)->getJson('/api/me')->assertOk()->assertJsonPath('data.email', $user->email);
    }

    public function test_admin_can_download_performance_report_csv(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->get(route('admin.reports.clippings'))->assertOk()->assertHeader('content-disposition');
    }
}
