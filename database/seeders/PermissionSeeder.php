<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\RolePermission;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = [
            'media.manage' => 'Mengelola media mitra.',
            'legal.verify' => 'Memverifikasi dokumen legalitas.',
            'contracts.manage' => 'Mengelola kontrak kerja sama.',
            'press.manage' => 'Mengelola siaran pers.',
            'clipping.review' => 'Memvalidasi kliping berita.',
            'journalists.manage' => 'Mengelola jurnalis mitra.',
            'reports.view' => 'Melihat dan mengunduh laporan.',
            'invoices.review' => 'Memeriksa e-Billing.',
            'payments.manage' => 'Mengelola pembayaran.',
            'settings.manage' => 'Mengelola pengaturan sistem.',
            'audit.view' => 'Melihat audit log.',
        ];

        foreach ($permissions as $name => $description) {
            Permission::updateOrCreate(['name' => $name], ['description' => $description]);
        }

        $rolePermissions = [
            'admin' => array_keys($permissions),
            'admin_media' => ['media.manage', 'legal.verify', 'contracts.manage', 'press.manage', 'clipping.review', 'journalists.manage', 'reports.view'],
            'admin_keuangan' => ['reports.view', 'invoices.review', 'payments.manage'],
        ];

        foreach ($rolePermissions as $role => $names) {
            foreach ($names as $name) {
                RolePermission::firstOrCreate([
                    'role' => $role,
                    'permission_id' => Permission::where('name', $name)->value('id'),
                ]);
            }
        }
    }
}
