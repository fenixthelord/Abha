<?php

namespace App\Http\Traits;

use GuzzleHttp\Client;
use Google_Client;

trait Firebase {
    /**
     * Handle data and send notification through Firebase Cloud Messaging (FCM).
     *
     * @param array $tokens
     * @param array $content
     * @return bool
     * @throws \Exception
     */
    public function HandelDataAndSendNotify($tokens, $content, $link = 'FLUTTER_NOTIFICATION_CLICK') {
        $client = new Client();

        try {
            if (empty($tokens)) {
                return false;
            }

            $title = $content['title'] ?? null;
            $body = $content['body'] ?? null;
            $image = $content['image'] ?? null;
            $data = $content['data'] ?? [];

            foreach ($tokens as $token) {
                $payload = [
                    'message' => [
                        'token' => $token,
                        'notification' => [
                            'title' => $title,
                            'body' => $body,
                            'image' => $image, // Add image support
                        ],
                        'data' => array_merge([
                            'click_action' => $link,
                        ], $data), // Include additional metadata
                        'apns' => [
                            'payload' => [
                                'aps' => [
                                    'sound' => "default",
                                ],
                            ],
                        ],
                    ],
                ];

                $response = $client->post('https://fcm.googleapis.com/v1/projects/smart-abha/messages:send', [
                    'headers' => [
                        'Authorization' => 'Bearer ' . $this->getAccessToken(),
                        'Content-Type' => 'application/json',
                        'Accept' => 'application/json',
                    ],
                    'json' => $payload,
                ]);

                if ($response->getStatusCode() !== 200) {
                    throw new \Exception(__('validation.custom.firebase.notification_failed') . $response->getStatusCode());
                }
            }

            return true;
        } catch (\Exception $e) {
            throw new \Exception(__('validation.custom.firebase.failed_to_send_notification') . $e->getMessage());
        }
    }
    function getAccessToken() {
        try {
            // Path to the service account key file
            $keyFilePath = storage_path('app/firebase/service_account.json');

            // Initialize the Google Client
            $client = new Google_Client();
            $client->setAuthConfig($keyFilePath);
            $client->addScope('https://www.googleapis.com/auth/cloud-platform');

            // Fetch the access token
            $accessToken = $client->fetchAccessTokenWithAssertion();

            if (isset($accessToken['access_token'])) {
                return $accessToken['access_token'];
            } else {
                throw new \Exception(__('messages.failed_to_obtain_token'));
            }
        } catch (\Exception $e) {
            throw new \Exception(__('messages.failed_to_obtain_token') . ': ' . $e->getMessage());
        }
    }
}
