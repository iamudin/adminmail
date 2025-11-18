<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class CloudflareService
{
    protected string $apiToken;
    protected string $zoneId;
    protected string $baseUrl = 'https://api.cloudflare.com/client/v4';

    public function __construct()
    {
        $this->apiToken = config('cloudflare.api_token');
        $this->zoneId = config('cloudflare.zone_id');
    }

    /**
     * Ambil daftar DNS records dari Cloudflare (dengan pencarian opsional)
     */
    public function listRecords(int $page = 1, int $perPage = 10, ?string $search = null): array
    {
        $params = [
            'page' => $page,
            'per_page' => $perPage,
        ];

        // Jika ada pencarian, tambahkan filter Cloudflare
        // Cloudflare mendukung filter `name` dan `content`
        if ($search) {
            $params['match'] = 'all';
            $params['name'] = $search;
            // Jika mau cari berdasarkan IP juga, bisa tambah content:
            // $params['content'] = $search;
        }

        $url = "{$this->baseUrl}/zones/{$this->zoneId}/dns_records";

        $response = Http::withToken($this->apiToken)
            ->get($url, $params)
            ->json();

        return $response;
    }

    /**
     * Membuat DNS record baru
     */
    public function createRecord(array $data): array
    {
        $url = "{$this->baseUrl}/zones/{$this->zoneId}/dns_records";
        return Http::withToken($this->apiToken)->post($url, $data)->json();
    }

    /**
     * Mengupdate DNS record
     */
    public function updateRecord(string $recordId, array $data): array
    {
        $url = "{$this->baseUrl}/zones/{$this->zoneId}/dns_records/{$recordId}";
        return Http::withToken($this->apiToken)->put($url, $data)->json();
    }

    /**
     * Menghapus DNS record
     */
    public function deleteRecord(string $recordId): array
    {
        $url = "{$this->baseUrl}/zones/{$this->zoneId}/dns_records/{$recordId}";
        return Http::withToken($this->apiToken)->delete($url)->json();
    }
}
