<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\Desk; 


class DeskController extends Controller
{
    public function index()
    {
        $apiKey = env('DESKS_API_KEY');
        $url = "http://127.0.0.1:8001/api/v2/{$apiKey}/desks";

        $response = Http::get($url);

        if ($response->failed()) {
            return response()->json(['error' => 'API error'], 500);
        }

        $deskIds = $response->json();

        foreach ($deskIds as $id) {
            Desk::updateOrCreate(
                ['desk_id' => $id],
            );
        }

        return response()->json([
            'message' => 'Data fetched successfully',
            'desks'   => $deskIds
        ]);
    }

    public function state($desk_id)
    {
        $apiKey = env('DESKS_API_KEY');
        $url = "http://127.0.0.1:8001/api/v2/{$apiKey}/desks/{$desk_id}";

        $response = Http::get($url);

        $deskData = $response->json();

        Desk::updateOrCreate(
            ['desk_id' => $desk_id],
            ['state' => $deskData['state']]
        );

        return response()->json($deskData);
    }

}
