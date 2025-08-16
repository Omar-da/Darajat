<?php

namespace App\Services\Firebase;

use Kreait\Firebase\Factory;

class FirebaseOAuth
{
    protected $auth;

    public function __construct()
    {
        $factory = (new Factory)
            ->withServiceAccount(storage_path('app/firebase/firebase-credentials.json'));

        $this->auth = $factory->createAuth();
    }

    public function verifyToken($idToken)
    {
        try {
            return $this->auth->verifyIdToken($idToken);
        } catch (\Exception $e) {
            return null;
        }
    }
}