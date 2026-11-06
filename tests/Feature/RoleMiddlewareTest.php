<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class RoleMiddlewareTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Route::middleware(['web', 'auth', 'role:admin,koordinator'])
            ->get('/_test/role', fn () => 'ok');
    }

    public function test_user_with_allowed_role_can_access_route(): void
    {
        foreach (['admin', 'koordinator'] as $role) {
            $user = User::factory()->create(['role' => $role]);

            $this->actingAs($user)->get('/_test/role')->assertOk();
        }
    }

    public function test_user_with_other_role_gets_forbidden(): void
    {
        foreach (['warga', 'donatur', 'relawan', 'nakes'] as $role) {
            $user = User::factory()->create(['role' => $role]);

            $this->actingAs($user)->get('/_test/role')->assertForbidden();
        }
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get('/_test/role')->assertRedirect('/login');
    }

    public function test_notifications_table_supports_database_notifications(): void
    {
        $this->assertTrue(Schema::hasColumns('notifications', ['id', 'type', 'notifiable_type', 'notifiable_id', 'data', 'read_at']));

        $user = User::factory()->create();

        $this->assertCount(0, $user->notifications);
    }
}
