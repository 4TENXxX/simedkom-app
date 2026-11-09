<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\MediaProfile;
use App\Models\Journalist;
use App\Models\Submission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthorizationAuditTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_status_change_is_recorded_in_audit_log(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $media = User::factory()->create(['role' => 'media']);
        $submission = Submission::create(['user_id' => $media->id, 'tahun_anggaran' => 2026, 'status' => 'pending']);

        $this->actingAs($admin)->patch(route('admin.submissions.update', $submission), [
            'status' => 'disetujui',
            'catatan_admin' => 'Dokumen lengkap.',
        ])->assertRedirect(route('admin.dashboard'));

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $admin->id,
            'action' => 'submission.status_changed',
            'auditable_type' => Submission::class,
            'auditable_id' => $submission->id,
        ]);
    }

    public function test_approved_submission_has_final_status_and_cannot_be_changed(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $submission = Submission::create([
            'user_id' => User::factory()->create(['role' => 'media'])->id,
            'tahun_anggaran' => 2026,
            'status' => 'disetujui',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.submissions.show', $submission))
            ->assertOk()
            ->assertSee('Keputusan final')
            ->assertDontSee('Simpan keputusan')
            ->assertDontSee('data-popup-form');

        $this->actingAs($admin)
            ->patch(route('admin.submissions.update', $submission), [
                'status' => 'ditolak',
                'catatan_admin' => 'Perubahan tidak boleh dilakukan.',
            ])
            ->assertForbidden();

        $this->assertDatabaseHas('submissions', [
            'id' => $submission->id,
            'status' => 'disetujui',
        ]);
    }

    public function test_media_cannot_access_audit_log(): void
    {
        $media = User::factory()->create(['role' => 'media']);

        $this->actingAs($media)->get(route('admin.audit-logs.index'))->assertForbidden();
        $this->assertSame(0, AuditLog::count());
    }

    public function test_granular_admin_role_can_access_admin_dashboard(): void
    {
        $adminMedia = User::factory()->create(['role' => 'admin_media']);

        $this->actingAs($adminMedia)->get(route('admin.dashboard'))->assertOk();
        $this->actingAs($adminMedia)->get(route('dashboard'))->assertRedirect(route('admin.dashboard'));
    }

    public function test_media_admin_cannot_access_finance_or_audit_boundary(): void
    {
        $adminMedia = User::factory()->create(['role' => 'admin_media']);

        $this->actingAs($adminMedia)->get(route('admin.invoices.index'))->assertForbidden();
        $this->actingAs($adminMedia)->get(route('admin.audit-logs.index'))->assertForbidden();
    }

    public function test_journalist_is_redirected_to_journalist_dashboard(): void
    {
        $journalist = User::factory()->create(['role' => 'jurnalis']);

        $this->actingAs($journalist)->get(route('dashboard'))
            ->assertRedirect(route('journalist.dashboard'));
        $this->actingAs($journalist)->get(route('journalist.dashboard'))->assertOk();
        $this->actingAs($journalist)->get(route('media.dashboard'))->assertForbidden();
    }

    public function test_super_admin_can_access_admin_dashboard(): void
    {
        $superAdmin = User::factory()->create(['role' => 'super_admin']);

        $this->actingAs($superAdmin)->get(route('admin.dashboard'))->assertOk();
    }

    public function test_media_without_approved_submission_is_redirected_from_operational_features(): void
    {
        foreach (['pending', 'perbaikan', 'ditolak'] as $status) {
            $media = User::factory()->create(['role' => 'media']);
            Submission::create([
                'user_id' => $media->id,
                'tahun_anggaran' => 2026,
                'status' => $status,
            ]);

            $this->actingAs($media)
                ->get(route('media.contracts.index'))
                ->assertRedirect(route('media.dashboard'));
        }
    }

    public function test_media_with_approved_submission_can_access_operational_features(): void
    {
        $media = User::factory()->create(['role' => 'media']);
        MediaProfile::create([
            'user_id' => $media->id,
            'nama_media' => 'Media Disetujui',
            'jenis_media' => 'online',
            'alamat' => 'Alamat',
            'no_whatsapp' => '0812',
        ]);
        Submission::create([
            'user_id' => $media->id,
            'tahun_anggaran' => 2026,
            'status' => 'disetujui',
        ]);

        $this->actingAs($media)->get(route('media.contracts.index'))->assertOk();
    }

    public function test_media_can_access_dashboard_and_submission_before_approval(): void
    {
        $media = User::factory()->create(['role' => 'media']);

        $this->actingAs($media)->get(route('media.dashboard'))->assertOk();
        $this->actingAs($media)->get(route('media.submissions.create'))->assertOk();
    }

    public function test_admin_can_view_media_detail_with_nested_journalists(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $media = User::factory()->create(['role' => 'media']);
        $profile = MediaProfile::create([
        'user_id' => $media->id,
        'nama_media' => 'Media Detail',
        'jenis_media' => 'online',
        'alamat' => 'Alamat media',
        'no_whatsapp' => '0812',
        ]);
        $submission = Submission::create([
        'user_id' => $media->id,
        'tahun_anggaran' => 2026,
        'status' => 'disetujui',
        ]);
        $journalistUser = User::factory()->create(['role' => 'jurnalis', 'name' => 'Jurnalis Detail']);
        Journalist::create([
        'user_id' => $journalistUser->id,
        'media_profile_id' => $profile->id,
        'position' => 'Reporter',
        'foto_wartawan_path' => 'journalists/foto.jpg',
        'sertifikat_kompetensi_path' => 'journalists/sertifikat.pdf',
        ]);

        $this->actingAs($admin)
        ->get(route('admin.submissions.show', $submission))
        ->assertOk()
        ->assertSee('Media Detail')
        ->assertSee('Jurnalis Detail')
        ->assertSee('Reporter');
    }
}
