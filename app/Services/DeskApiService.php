<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class DeskApiService
{
    protected $baseUrl;
    protected $apiKey;

    public function __construct()
    {
        $this->baseUrl = env('API_BASE', 'http://localhost:8001/api/v2');
        $this->apiKey = env('DESKS_API_KEY', 'default-api-key');
    }

    /**
     * Get all desk IDs from the API
     * 
     * @return array|null Array of desk IDs or null on failure
     */
    public function getAllDeskIds(): ?array
    {
        try {
            $response = Http::timeout(10)->get("{$this->baseUrl}/{$this->apiKey}/desks");

            if ($response->successful()) {
                return $response->json();
            }

            Log::error('Failed to fetch desk IDs from API', [
                'status' => $response->status(),
                'body' => $response->body()
            ]);

            return null;
        } catch (\Exception $e) {
            Log::error('Exception while fetching desk IDs from API', [
                'error' => $e->getMessage()
            ]);

            return null;
        }
    }

    /**
     * Get specific desk data from the API
     * 
     * @param string $deskId The desk ID to fetch
     * @return array|null Desk data or null on failure
     */
    public function getDeskData(string $deskId): ?array
    {
        try {
            $response = Http::timeout(10)->get("{$this->baseUrl}/{$this->apiKey}/desks/{$deskId}");

            if ($response->successful()) {
                return $response->json();
            }

            Log::error('Failed to fetch desk data from API', [
                'desk_id' => $deskId,
                'status' => $response->status(),
                'body' => $response->body()
            ]);

            return null;
        } catch (\Exception $e) {
            Log::error('Exception while fetching desk data from API', [
                'desk_id' => $deskId,
                'error' => $e->getMessage()
            ]);

            return null;
        }
    }

    /**
     * Update desk position via API
     * 
     * @param string $deskId The desk ID to update
     * @param int $positionMm The new position in millimeters
     * @return bool True on success, false on failure
     */
    public function updateDeskPosition(string $deskId, int $positionMm): bool
    {
        try {
            $response = Http::timeout(10)
                ->withHeaders(['Content-Type' => 'application/json'])
                ->put(
                    "{$this->baseUrl}/{$this->apiKey}/desks/{$deskId}/state",
                    ['position_mm' => $positionMm]
                );

            if ($response->successful()) {
                return true;
            }

            Log::error('Failed to update desk position via API', [
                'desk_id' => $deskId,
                'position_mm' => $positionMm,
                'status' => $response->status(),
                'body' => $response->body()
            ]);

            return false;
        } catch (\Exception $e) {
            Log::error('Exception while updating desk position via API', [
                'desk_id' => $deskId,
                'position_mm' => $positionMm,
                'error' => $e->getMessage()
            ]);

            return false;
        }
    }
}
