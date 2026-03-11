<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Feature tests for the /test-dave API endpoint.
 *
 * Tests verify that the endpoint returns the correct
 * JSON response with Dave's greeting message.
 *
 * @package Tests\Feature
 */
final class TestDaveEndpointTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that the /test-dave endpoint returns a successful response.
     *
     * @return void
     */
    public function test_test_dave_endpoint_returns_successful_response(): void
    {
        $response = $this->getJson('/api/test-dave');

        $response->assertStatus(200);
    }

    /**
     * Test that the /test-dave endpoint returns the correct JSON structure.
     *
     * @return void
     */
    public function test_test_dave_endpoint_returns_correct_json_structure(): void
    {
        $response = $this->getJson('/api/test-dave');

        $response->assertJsonStructure([
            'message',
        ]);
    }

    /**
     * Test that the /test-dave endpoint returns the correct greeting message.
     *
     * @return void
     */
    public function test_test_dave_endpoint_returns_correct_message(): void
    {
        $response = $this->getJson('/api/test-dave');

        $response->assertJson([
            'message' => 'Hello from Dave!',
        ]);
    }
}
