<?php

namespace Tests\Feature;

use App\Models\AppNotification;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotificationClippingFileTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_mark_only_own_notification_as_read(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();
        $notification = AppNotification::create(['user_id' => $user->id, 'title' => 'Info', 'message' => 'Pesan']);
        $otherNotification = AppNotification::create(['user_id' => $other->id, 'title' => 'Info', 'message' => 'Pesan']);

        $this->actingAs($user)->patch(route('notifications.update', $notification), ['read' => true])->assertRedirect();
        $this->assertNotNull($notification->fresh()->read_at);
        $this->actingAs($user)->patch(route('notifications.update', $otherNotification), ['read' => true])->assertForbidden();
    }
}
