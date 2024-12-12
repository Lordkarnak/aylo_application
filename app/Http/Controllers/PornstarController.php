<?php

namespace App\Http\Controllers;

use App\Http\Resources\PornstarResource;
use App\Models\Pornstar;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class PornstarController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        return PornstarResource::collection(Pornstar::all());
    }

    public function show($id): PornstarResource
    {
        return new PornstarResource(Pornstar::findOrFail($id));
    }

    public function refresh(): JsonResponse
    {
        $url = '';
        // service
        return response()->json(['message' => 'Local cache refreshed successfully.']);
    }
}
