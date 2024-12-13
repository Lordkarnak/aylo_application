<?php

namespace App\Http\Controllers;

use App\Http\Resources\PornstarResource;
use App\Models\Pornstar;
use App\Services\PornstarService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class PornstarController extends Controller
{
    public function index(): JsonResponse
    {
        $response = PornstarResource::collection(Pornstar::all());

        if (empty($response)) {
            $response = ['message' => 'Could not find any pornstar.'];
        }

        return response()->json($response);
    }

    public function show($id): JsonResponse
    {
        try {
            $response = new PornstarResource(Pornstar::findOrFail($id));
        } catch (ModelNotFoundException $e) {
            $response = ['message' => 'Could not find pornstar with id ' . $id];
        } catch (\Exception $e) {
            $response = $e->getMessage();
        }

        return response()->json($response);
    }

    public function refreshData(): JsonResponse
    {
        $url = "https://ph-c3fuhehkfqh6huc0.z01.azurefd.net/feed_pornstars.json";
        
        $service = new PornstarService();
        $items = $service->fetch($url);
        $service->store($items);
        $service->cache();

        // service
        return response()->json(['message' => 'Local data refreshed successfully.']);
    }

    public function refreshCache(): JsonResponse
    {
        $service = new PornstarService();
        $service->cache();

        // service
        return response()->json(['message' => 'Local cache refreshed successfully.']);
    }
}
