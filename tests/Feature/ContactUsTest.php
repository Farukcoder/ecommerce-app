<?php

namespace Tests\Feature;

use App\Models\ContactUsMessage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContactUsTest extends TestCase
{
    use RefreshDatabase;

    public function test_website_can_submit_contact_us_message(): void
    {
        $response = $this->postJson('/api/home/contact-us', [
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'phone' => '+880 1712345678',
            'message' => 'I would like to know more about your products.',
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.status', 'new')
            ->assertJsonStructure([
                'message',
                'data' => ['id', 'status', 'created_at'],
            ]);

        $this->assertDatabaseHas('contact_us_messages', [
            'email' => 'jane@example.com',
            'status' => 'new',
        ]);
    }

    public function test_contact_us_requires_all_fields(): void
    {
        $response = $this->postJson('/api/home/contact-us', []);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['name', 'email', 'phone', 'message']);
    }

    public function test_authenticated_admin_can_view_contact_us_index(): void
    {
        $user = User::factory()->create();

        ContactUsMessage::query()->create([
            'name' => 'John',
            'email' => 'john@example.com',
            'phone' => '+880 1711111111',
            'message' => 'Hello',
            'status' => 'new',
        ]);

        $response = $this->actingAs($user)->get(route('contact-us.index'));

        $response->assertOk()
            ->assertSee('john@example.com');
    }

    public function test_viewing_message_marks_new_as_read(): void
    {
        $user = User::factory()->create();

        $message = ContactUsMessage::query()->create([
            'name' => 'John',
            'email' => 'john@example.com',
            'phone' => '+880 1711111111',
            'message' => 'Hello',
            'status' => 'new',
        ]);

        $this->actingAs($user)->get(route('contact-us.show', $message));

        $message->refresh();

        $this->assertSame('read', $message->status);
        $this->assertNotNull($message->read_at);
    }
}
