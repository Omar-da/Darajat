<?php

namespace App\Services\Firebase;

use Illuminate\Support\Facades\Notification;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;

class FCMService
{
    protected $messaging;

    protected $userRepository;

    public function __construct()
    {
        $firebase = (new Factory)
            ->withServiceAccount(storage_path('app/firebase/firebase-credentials.json'));

        $this->messaging = $firebase->createMessaging();
    }



    public function sendNotification($deviceToken, $title, $body, array $data = []): array
    {
        $notification = Notification::create($title, $body);

        $message = CloudMessage::withTarget('token', $deviceToken)
            ->withNotification($notification)
            ->withData($data);

        return $this->messaging->send($message);
    }
}
