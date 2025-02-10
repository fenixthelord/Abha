<?php
namespace app\services;
use  Illuminate\Support\Facades\Http;
class NotificationService
{
    protected $baseUrl;

    public function __construct()
    {
        // Base URL for Smart ABHA Notification Service
        $this->baseUrl = env('NOTIFY_SERV');

    }

    public function Postcall($method,$params)
    {

        $response = Http::WithHeaders([
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ])->post("http://smartabha-notification.test/api/".$method,$params);

        return $response->json();
    }
    public function Getcall($method,$params)
    {
        $response = Http::WithHeaders([
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ])->get("http://smartabha-notification.test/api/".$method,$params);

        return $response->json();
    }
}
