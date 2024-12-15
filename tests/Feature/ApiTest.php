<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ApiTest extends TestCase
{
    /**
     * Test that api responds to json
     */
    public function test_api_responds_with_json(): void
    {
        $response = $this->get('/api/pornstars');
        $response->assertStatus(200);
        $this->assertJson($response->content());

        $id = fake()->randomNumber();
        $response = $this->get('/api/pornstars/' . $id);
        $response->assertStatus(200);
        $this->assertJson($response->content());

        $id = fake()->randomNumber();
        $thumbId = fake()->randomNumber();
        $response = $this->get('/api/pornstars/' . $id . '/thumbnails/' . $thumbId);
        $response->assertStatus(200);
        $this->assertJson($response->content());

        $id = fake()->randomNumber();
        $response = $this->post('/api/pornstars/' . $id . '/refreshCache');
        $response->assertStatus(200);
        $this->assertJson($response->content());
    }

    /**
     * Test that api denies post requests on the get routes
     * @return void
     */
    public function test_api_denies_post_request(): void
    {
        $response = $this->post('/api/pornstars');
        $response->assertBadRequest();

        $id = fake()->randomNumber();
        $response = $this->post('/api/pornstars/' . $id);
        $response->assertBadRequest();

        $id = fake()->randomNumber();
        $thumbId = fake()->randomNumber();
        $response = $this->post('/api/pornstars/' . $id . 'thumbnails/' . $thumbId);
        $response->assertBadRequest();
    }

    /**
     * Test that api denies get requests on the post routes
     * @return void
     */
    public function test_api_denies_get_request(): void
    {
        $id = fake()->randomNumber();
        $response = $this->post('/api/pornstars/' . $id . '/refreshCache');
        $response->assertBadRequest();
    }
}
