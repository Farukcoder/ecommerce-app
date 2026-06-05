<?php

namespace Tests\Feature;

use App\Models\SupportTicket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SupportTicketTest extends TestCase
{
    use RefreshDatabase;

    public function test_website_can_submit_support_ticket(): void
    {
        $response = $this->postJson('/api/home/support-tickets', [
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'phone' => '+880 1712345678',
            'subject' => 'order',
            'order_number' => 'ORD-2026-00001',
            'message' => 'I need help with my recent order.',
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.status', 'open')
            ->assertJsonStructure([
                'message',
                'data' => ['id', 'ticket_number', 'status', 'created_at'],
            ]);

        $this->assertDatabaseHas('support_tickets', [
            'email' => 'jane@example.com',
            'subject' => 'order',
            'order_number' => 'ORD-2026-00001',
            'status' => 'open',
        ]);
    }

    public function test_subjects_endpoint_returns_configured_options(): void
    {
        $response = $this->getJson('/api/home/support-tickets/subjects');

        $response->assertOk()
            ->assertJsonFragment(['value' => 'general', 'label' => 'General inquiry']);
    }

    public function test_authenticated_admin_can_view_support_tickets_index(): void
    {
        $user = User::factory()->create();

        SupportTicket::query()->create([
            'name' => 'John',
            'email' => 'john@example.com',
            'phone' => '+880 1711111111',
            'subject' => 'general',
            'message' => 'Hello',
            'status' => 'open',
        ]);

        $response = $this->actingAs($user)->get(route('support-tickets.index'));

        $response->assertOk()
            ->assertSee('john@example.com');
    }
}
