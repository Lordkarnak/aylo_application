<?php

namespace Tests\Unit;

use App\Services\PornstarService;
use PHPUnit\Framework\TestCase;

class PornstarServiceTest extends TestCase
{
    /**
     * A basic unit test example.
     */
    public function test_fetch(): void
    {
        $service = new PornstarService();
        $result = $service->fetch('');
        $this->assertIsArray($result, 'Result of fetch is array?');
        $this->assertEmpty($result, 'Result of fetch is empty?');

        $url = "https://ph-c3fuhehkfqh6huc0.z01.azurefd.net/feed_pornstars.json";
        $result = $service->fetch($url);
        $this->assertIsArray($result, 'Result of fetch is array?');
        $this->assertIsArray($result[0], 'Result is array of arrays?');
        $this->assertArrayHasKey('id', $result[0], 'Result of fetch has id?');
    }
}
