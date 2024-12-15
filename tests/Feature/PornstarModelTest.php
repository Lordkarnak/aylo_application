<?php

namespace Tests\Feature;

use App\Models\Pornstar;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class PornstarModelTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic feature test example.
     */
    public function test_pornstar_saves_in_db(): void
    {
        $pornstar = Pornstar::factory()->make([
            'name' => 'Judy'
        ]);
        $this->assertDatabaseCount('pornstars', 1);

        $pornstar->delete();
        $this->assertDatabaseMissing('pornstars', ['name' => 'Judy']);
    }
}
