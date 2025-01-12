<?php

namespace Tests\Feature;

use Tests\TestCase;

class SendNotificationTest extends TestCase
{
    /**
     * اختبار عند عدم وجود التوكنات (tokens).
     */
    public function testSendNotificationFailsWithoutTokens()
    {
        $response = $this->postJson('/api/send-notification', [
            'title' => 'Test Title',
            'body' => 'Test Body',
        ]);

        $response->assertStatus(500); // تأكد من أن الرد يحتوي على خطأ
        $response->assertJson(['message' => 'Tokens are required.']);
    }

    /**
     * اختبار عند عدم وجود العنوان أو النص.
     */
    public function testSendNotificationFailsWithoutTitleOrBody()
    {
        $response = $this->postJson('/api/send-notification', [
            'tokens' => ['token1', 'token2'],
        ]);

        $response->assertStatus(400);
        $response->assertJson(['message' => 'Title and body are required.']);
    }

    /**
     * اختبار نجاح الإشعار.
     */
    public function testSendNotificationSucceedsWithValidData()
    {
        // محاكاة لدالة HandelDataAndSendNotify
        $this->mock(SomeClass::class, function ($mock) {
            $mock->shouldReceive('HandelDataAndSendNotify')->once()->andReturn(true);
        });

        $response = $this->postJson('/api/send-notification', [
            'tokens' => ['token1', 'token2'],
            'title' => 'Test Title',
            'body' => 'Test Body',
        ]);

        $response->assertStatus(200);
        $response->assertJson(['message' => 'Notifications sent successfully!']);
    }

    /**
     * اختبار عند حدوث استثناء.
     */
    public function testSendNotificationHandlesException()
    {
        // محاكاة لرمي استثناء داخل HandelDataAndSendNotify
        $this->mock(SomeClass::class, function ($mock) {
            $mock->shouldReceive('HandelDataAndSendNotify')
                ->once()
                ->andThrow(new \Exception('Something went wrong.'));
        });

        $response = $this->postJson('/api/send-notification', [
            'tokens' => ['token1', 'token2'],
            'title' => 'Test Title',
            'body' => 'Test Body',
        ]);

        $response->assertStatus(500);
        $response->assertJson(['message' => 'Something went wrong.']);
    }
}
