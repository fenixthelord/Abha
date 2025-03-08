<?php

namespace Tests\Feature;

use Tests\TestCase;

class SendNotificationTest extends TestCase
{
/*
* اختبار عند عدم وجود التوكنات (tokens).
*/
    public function testSendNotificationFailsWithoutTokens()
    {
        $response = $this->postJson('/api/send-notification', [
            'title' => 'Test Title',
            'body' => 'Test Body',
        ]);

        $response->assertStatus(400); // تأكد من أن الرد يحتوي على خطأ
        // $response->assertJson(['msg' => 'Tokens are required.']);
    }

/*
* اختبار عند عدم وجود العنوان أو النص.
*/
    public function testSendNotificationFailsWithoutTitleOrBody()
    {
        $response = $this->postJson('/api/send-notification', [
            'tokens' => ['token1', 'token2'],
        ]);

        $response->assertStatus(400);
        // $response->assertJson(['message' => 'Title and body are required.']);
    }

    public function testSendNotificationSucceedsWithValidData()
    {
//        إعداد البيانات التجريبية (Input Data)
        $payload = [
            'tokens' => ['token1', 'token2'], // الرموز المستهدفة للإشعارات
            'title' => 'Test Title',         // عنوان الإشعار
            'body' => 'Test Body',           // نص الإشعار
            'data' => [                      // بيانات إضافية للإشعار
                'type' => 'notification_type',
                'object' => 'object_data',
                'screen' => 'screen_name',
            ],
        ];

        // إرسال طلب POST إلى دالة sendNotification
        $response = $this->postJson('/api/send-notification', $payload);

        // التحقق من الاستجابة
        $response->assertStatus(200); // التأكد من أن الاستجابة تعيد حالة HTTP 200
        $response->assertJson([
            'status' => true,
            'code' => 200,
            'msg' => 'Notifications sent successfully!',
        ]);
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
