<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class CpanelApiService
{
    protected $baseUrl;
    protected $token;
    protected $username;

    public function __construct()
    {
        $this->baseUrl = config('cpanel.host');     // contoh: https://domainanda.com:2083
        $this->token = config('cpanel.token');      // API Token
        $this->username = config('cpanel.username'); // nama user cPanel
    }

    private function request($endpoint, $params = [])
    {
        $response = Http::withHeaders([
            'Authorization' => "cpanel {$this->username}:{$this->token}"
        ])->get("{$this->baseUrl}/execute/{$endpoint}", $params);

        if ($response->failed()) {
            throw new \Exception("API Error: " . $response->body());
        }

        return $response->json();
    }

    public function listEmails()
    {
        $result = $this->request('Email/list_pops');
        return $result['data'] ?? [];
    }

    public function createEmail($email, $password, $quota = 250)
    {
        $user = explode('@', $email)[0];
        $domain = explode('@', $email)[1];

        return $this->request('Email/add_pop', [
            'email' => $user,
            'password' => $password,
            'quota' => $quota,
            'domain' => $domain,
        ]);
    }

    public function deleteEmail($email)
    {
        $user = explode('@', $email)[0];
        $domain = explode('@', $email)[1];

        return $this->request('Email/delete_pop', [
            'email' => $user,
            'domain' => $domain,
        ]);
    }

    public function changePassword($email, $password)
    {
        $user = explode('@', $email)[0];
        $domain = explode('@', $email)[1];

        return $this->request('Email/passwd_pop', [
            'email' => $user,
            'domain' => $domain,
            'password' => $password,
        ]);
    }
}
