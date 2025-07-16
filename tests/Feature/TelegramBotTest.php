<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class TelegramBotTest extends TestCase
{
    /**
     * Test that the webhook endpoint accepts POST requests.
     *
     * @return void
     */
    public function test_webhook_accepts_post_requests()
    {
        $response = $this->postJson('/api/telegram/webhook', [
            'update_id' => 123456789,
            'message' => [
                'message_id' => 1,
                'from' => [
                    'id' => 123456789,
                    'first_name' => 'Test',
                    'username' => 'testuser',
                ],
                'chat' => [
                    'id' => 123456789,
                    'first_name' => 'Test',
                    'username' => 'testuser',
                    'type' => 'private',
                ],
                'date' => time(),
                'text' => '/start',
            ],
        ]);

        $response->assertStatus(200)
                 ->assertJson(['status' => 'success']);
    }
}
