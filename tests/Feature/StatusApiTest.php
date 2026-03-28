<?php

namespace Tests\Feature;

use Tests\TestCase;

class StatusApiTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function test_the_application_returns_a_successful_response(): void
    {
        $response = $this->get("/api/status");

        $response->assertStatus(200);
    }
}
