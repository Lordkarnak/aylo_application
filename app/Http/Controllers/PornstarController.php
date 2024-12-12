<?php

namespace App\Http\Controllers;

use App\Http\Resources\PornstarResource;
use App\Models\Pornstar;
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

    public function refresh(): JsonResponse
    {
        $url = '';
        // service
        return response()->json(['message' => 'Local cache refreshed successfully.']);
    }
}
