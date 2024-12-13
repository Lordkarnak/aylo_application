<?php

namespace App\Http\Controllers;

use App\Http\Resources\PornstarResource;
use App\Models\Pornstar;
use App\Services\PornstarService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\File;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;

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

    /**
     * Retrieve a thumbnail and display it on a browser, don't know why anyone would want this.
     * Anyways, this method ensures the cache is working.
     * @param \Illuminate\Http\Response $response
     * @param string $id
     * @param string $thumb_id
     * @return JsonResponse|mixed|Response
     */
    public function getThumbnail(Response $response, string $id, string $thumb_id)
    {
        $key = 'thumb_' . $id . '_' . $thumb_id;

        // attempt to refresh cache and find the thumbnail
        if (!Cache::has($key)) {
            $service = new PornstarService();
            $service->cacheByPornstar(Pornstar::find($id));
        }

        // attempt to return the image
        if (Cache::has($key)) {
            return response()->make(Cache::get($key), 200, ['Content-Type' => 'image/png']);
        }
        
        return response()->json(['message' => 'Thumbnail not found.']);
    }

    /**
     * Dangerous method that could be abused by the api when a user forces a recreation of the refresh data.
     * Possible cause of a DOS attack. Best to use the laravel command for refreshing the data.
     * @return \Illuminate\Http\JsonResponse
     */
    private function refreshData(): JsonResponse
    {
        $service = new PornstarService();
        $items = $service->fetch(Config::get('app.feed_url'));
        $service->store($items);
        $service->cache();

        // service
        return response()->json(['message' => 'Local data refreshed successfully.']);
    }

    /**
     * Refresh the cache for a given pornstar manually
     * @param \App\Models\Pornstar $pornstar
     * @return \Illuminate\Http\JsonResponse
     */
    public function refreshCache(Pornstar $pornstar): JsonResponse
    {
        $service = new PornstarService();
        $service->cacheByPornstar($pornstar);

        // service
        return response()->json(['message' => 'Local cache for ' . $pornstar->name . ' refreshed successfully.']);
    }
}
