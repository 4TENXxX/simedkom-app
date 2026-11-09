<?php

namespace Tests\Feature;

use App\Models\Permission;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PermissionTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_media_can_access_contracts_but_not_finance(): void
    {
        $this->seed(PermissionSeeder::class);
        $user = User::factory()->create(['role' => 'admin_media']);

        $this->assertTrue($user->hasPermission('contracts.manage'));
        $this->assertFalse($user->hasPermission('invoices.review'));
        $this->actingAs($user)->get(route('admin.invoices.index'))->assertForbidden();
    }

    public function test_admin_can_access_permission_protected_contract_route(): void
    {
        $this->seed(PermissionSeeder::class);
        $user = User::factory()->create(['role' => 'admin']);

        $this->actingAs($user)->get(route('admin.contracts.index'))->assertOk();
        $this->assertNotNull(Permission::where('name', 'contracts.manage')->first());
    }
}
