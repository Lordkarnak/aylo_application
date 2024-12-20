<?php

namespace App\Http\Controllers;

use App\Models\Pornstar;
use App\Services\PornstarService;
use Illuminate\Http\File;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PornstarController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        $service = new PornstarService();

        $pornstars = Pornstar::paginate(15);
        
        foreach ($pornstars as $pornstar) {
            try {
                $pornstar->cached_thumbnail = $service->retrieveCachedImage($pornstar->id);
            } catch (\Exception $e) {
                // do nothing
            }
        }

        return view('pornstars.index')
        ->with('pornstars', $pornstars);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
