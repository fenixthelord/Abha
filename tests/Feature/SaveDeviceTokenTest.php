<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\DeviceToken;

class SaveDeviceTokenTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test saving a device token successfully.
     *
     * @return void
     */
    public function testSaveDeviceTokenSuccessfully()
    {


        // إنشاء مستخدم لاختبار الحقل user_id
        $user = User::factory()->create();
        // إرسال طلب لحفظ رمز الجهاز
        $response = $this->postJson('/api/save-device-token', [
            'token' => 'device1223token',
            'user_id' => $user->id,
        ]);

        // تأكد من أن الاستجابة ناجحة
        $response->assertStatus(200);
//        $response->assertJson(['msg' => 'Token saved successfully']);

        // تحقق من إدخال البيانات في قاعدة البيانات
        $this->assertDatabaseHas('device_tokens', [
            'token' => 'device1223token',
            'user_id' => $user->id,
        ]);
    }

    /**
     * Test saving a device token with a missing token field.
     *
     * @return void
     */
    public function testSaveDeviceTokenFailsWithoutToken()
    {
        $response = $this->postJson('/api/save-device-token', [
            'user_id' => 1,
        ]);

        // تأكد من أن الاستجابة تحتوي على خطأ في التحقق
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['token']);
    }

    /**
     * Test saving a device token with a duplicate token.
     *
     * @return void
     */
    public function testSaveDeviceTokenFailsWithDuplicateToken()
    {
        // إضافة رمز جهاز مسبقًا في قاعدة البيانات
        DeviceToken::create([
            'token' => 'unique-device-token-12345',
            'user_id' => null,
        ]);

        // إرسال طلب مع رمز مكرر
        $response = $this->postJson('/api/save-device-token', [
            'token' => 'unique-device-token-12345',
        ]);

        // تحقق من أن الاستجابة تحتوي على خطأ
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['token']);
    }
}
