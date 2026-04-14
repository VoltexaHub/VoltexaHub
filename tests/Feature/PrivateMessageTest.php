<?php

namespace Tests\Feature;

use App\Models\Conversation;
use App\Models\User;
use App\Notifications\NewPrivateMessage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class PrivateMessageTest extends TestCase
{
    use RefreshDatabase;

    public function test_sending_a_message_creates_a_conversation_and_notifies_recipient(): void
    {
        Notification::fake();

        $me = User::factory()->create();
        $them = User::factory()->create();

        $this->actingAs($me)->post('/messages', [
            'recipient_id' => $them->id,
            'body' => 'Hi there',
        ])->assertRedirect();

        $this->assertDatabaseCount('conversations', 1);
        $this->assertDatabaseHas('messages', ['user_id' => $me->id, 'body' => 'Hi there']);
        Notification::assertSentTo($them, NewPrivateMessage::class);
        Notification::assertNotSentTo($me, NewPrivateMessage::class);
    }

    public function test_sending_a_second_message_to_same_recipient_reuses_conversation(): void
    {
        $me = User::factory()->create();
        $them = User::factory()->create();

        $this->actingAs($me)->post('/messages', ['recipient_id' => $them->id, 'body' => 'one']);
        $this->actingAs($me)->post('/messages', ['recipient_id' => $them->id, 'body' => 'two']);

        $this->assertDatabaseCount('conversations', 1);
        $this->assertDatabaseCount('messages', 2);
    }

    public function test_outsider_cannot_view_conversation(): void
    {
        $a = User::factory()->create();
        $b = User::factory()->create();
        $outsider = User::factory()->create();

        $conversation = Conversation::create(['last_message_at' => now()]);
        $conversation->participants()->attach([$a->id, $b->id]);

        $this->actingAs($outsider)->get("/messages/{$conversation->id}")->assertForbidden();
    }

    public function test_cannot_message_yourself(): void
    {
        $me = User::factory()->create();

        $this->actingAs($me)->from('/messages/new')->post('/messages', [
            'recipient_id' => $me->id,
            'body' => 'Hi me',
        ])->assertRedirect('/messages/new');

        $this->assertDatabaseCount('conversations', 0);
    }
}
