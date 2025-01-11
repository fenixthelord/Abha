<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Traits\Firebase;
use App\Http\Traits\ResponseTrait;
use Illuminate\Http\Request;

class NotificationController extends Controller {
    use Firebase, ResponseTrait;
    public function sendNotification(Request $request) {
        // استقبال البيانات من الطلب
        $tokens = $request->input('tokens', []);
        $content = [
            'title' => $request->input('title'),
            'body' => $request->input('body'),
            'type' => $request->input('data.type', null), // (اختياري)
            'object' => $request->input('data.object', null), // (اختياري)
            'screen' => $request->input('data.screen', null), // (اختياري)
        ];

        // التحقق من التوكينات والعنوان والمحتوى
        if (empty($tokens)) {
            return $this->returnError('Tokens are required.');
        }
        if (empty($content['title']) || empty($content['body'])) {
            return $this->returnError('Title and body are required.');
        }

        // إرسال الإشعارات عبر الـ Trait
        try {
            $status = $this->HandelDataAndSendNotify($tokens, $content);
            return $status
                ? $this->returnSuccessMessage('Notifications sent successfully!')
                : $this->returnError('Failed to send notifications.');
        } catch (\Exception $e) {
            return $this->returnError($e->getMessage());
        }
    }
}
