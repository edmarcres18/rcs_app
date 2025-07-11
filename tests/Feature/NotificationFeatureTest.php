<?php

namespace Tests\Feature;

use App\Models\Instruction;
use App\Models\User;
use App\Notifications\InstructionAssigned;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class NotificationFeatureTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function instruction_assigned_notification_is_sent_and_queued()
    {
        // 1. Arrange
        Notification::fake();

        $sender = User::factory()->create();
        $recipient = User::factory()->create();

        // Create an instruction and attach the recipient
        // We eager load sender to simulate the optimized controller logic
        $instruction = Instruction::factory()->create(['sender_id' => $sender->id])->load('sender');
        $instruction->recipients()->attach($recipient->id);

        // 2. Act
        // Send the notification to the recipient.
        Notification::send($recipient, new InstructionAssigned($instruction));

        // 3. Assert
        // Assert that a notification was sent to the given user.
        Notification::assertSentTo(
            $recipient,
            InstructionAssigned::class,
            function ($notification, $channels) use ($instruction) {
                // Assert it's sent via the correct channels
                $this->assertContains('database', $channels);
                $this->assertContains('broadcast', $channels);
                $this->assertContains('mail', $channels);

                // Assert the notification has the correct data
                $this->assertEquals($instruction->id, $notification->instruction->id);

                // Assert the notification is on the queue
                $this->assertTrue(in_array(\Illuminate\Contracts\Queue\ShouldQueue::class, class_implements($notification)));

                return true;
            }
        );

        // Assert that the notification was sent exactly once
        Notification::assertCount(1);
    }
}
