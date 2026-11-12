<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Spatie\Activitylog\Models\Activity;
use Tests\TestCase;

class AccessAndPrivacyTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_sent_to_the_login(): void
    {
        $this->get('/')->assertRedirect('/login');
    }

    public function test_inactive_users_cannot_use_the_backoffice(): void
    {
        $this->actingAs(User::factory()->create(['is_active' => false]))
            ->get('/')
            ->assertForbidden();
    }

    public function test_team_members_cannot_open_admin_areas(): void
    {
        $this->actingAs(User::factory()->create());

        $this->get('/pipeline')->assertOk();
        $this->get('/cases')->assertOk();
        $this->get('/tasks')->assertOk();
        $this->get('/billing')->assertForbidden();
        $this->get('/users')->assertForbidden();
        $this->get('/task-templates')->assertForbidden();
        $this->get('/audit-log')->assertForbidden();
    }

    public function test_admins_can_open_admin_areas(): void
    {
        $this->actingAs(User::factory()->admin()->create());

        $this->get('/billing')->assertOk();
        $this->get('/users')->assertOk();
        $this->get('/task-templates')->assertOk();
        $this->get('/audit-log')->assertOk();
    }

    public function test_client_contact_data_is_encrypted_and_kept_out_of_the_audit_log(): void
    {
        $client = Client::factory()->create(['email' => 'person@example.com', 'phone' => '0170 1234567']);

        $raw = DB::table('clients')->where('id', $client->id)->first();
        $this->assertStringNotContainsString('person@example.com', $raw->email);
        $this->assertStringNotContainsString('1234567', $raw->phone);

        $logged = json_encode(Activity::query()->where('subject_type', Client::class)->pluck('properties'));
        $this->assertStringNotContainsString('person@example.com', $logged);
        $this->assertStringNotContainsString('1234567', $logged);
    }

    public function test_viewing_a_client_is_recorded(): void
    {
        $user = User::factory()->create();
        $client = Client::factory()->create();

        $this->actingAs($user)->get("/clients/{$client->id}")->assertOk();

        $this->assertTrue(Activity::query()
            ->where('event', 'viewed')
            ->where('subject_id', $client->id)
            ->where('causer_id', $user->id)
            ->exists());
    }
}
