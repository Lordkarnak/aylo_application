<?php

namespace App\Http\Controllers;

use App\Http\Resources\PornstarResource;
use App\Models\Pornstar;
use Illuminate\Http\Request;

class PornstarController extends Controller
{
    public function index()
    {
        return PornstarResource::collection(Pornstar::all());
    }
}
