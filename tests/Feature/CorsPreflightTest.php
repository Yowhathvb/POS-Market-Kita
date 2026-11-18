<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CorsPreflightTest extends TestCase
{
    use RefreshDatabase;

    protected string $apiKey = 'pos_secret_token_1234567890';

    protected function setUp(): void
    {
        parent::setUp();
        config(['app.api_key' => $this->apiKey]);
        putenv("API_KEY={$this->apiKey}");
    }

    public function test_options_preflight_on_auth_register_returns_cors_headers(): void
    {
        $response = $this->call(
            'OPTIONS',
            '/auth/register',
            [],
            [],
            [],
            [
                'HTTP_ORIGIN' => 'http://localhost:3002',
                'HTTP_ACCESS_CONTROL_REQUEST_METHOD' => 'POST',
                'HTTP_ACCESS_CONTROL_REQUEST_HEADERS' => 'content-type, x-api-key',
            ]
        );

        $response->assertStatus(204);
        $this->assertTrue(
            $response->headers->get('Access-Control-Allow-Origin') === 'http://localhost:3002' ||
            $response->headers->get('Access-Control-Allow-Origin') === '*'
        );
        $this->assertNotNull($response->headers->get('Access-Control-Allow-Methods'));
    }

    public function test_options_preflight_on_v1_auth_register_returns_cors_headers(): void
    {
        $response = $this->call(
            'OPTIONS',
            '/v1/auth/register',
            [],
            [],
            [],
            [
                'HTTP_ORIGIN' => 'http://localhost:3002',
                'HTTP_ACCESS_CONTROL_REQUEST_METHOD' => 'POST',
                'HTTP_ACCESS_CONTROL_REQUEST_HEADERS' => 'content-type, x-api-key',
            ]
        );

        $response->assertStatus(204);
        $this->assertTrue(
            $response->headers->get('Access-Control-Allow-Origin') === 'http://localhost:3002' ||
            $response->headers->get('Access-Control-Allow-Origin') === '*'
        );
    }

    public function test_post_auth_register_succeeds_with_cors_headers(): void
    {
        $response = $this->postJson('/auth/register', [
            'nama' => 'Frontend Developer',
            'username' => 'fe_developer',
            'email' => 'fe@example.com',
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
        ], [
            'Origin' => 'http://localhost:3002',
            'X-API-KEY' => $this->apiKey,
        ]);

        $response->assertStatus(201);
        $this->assertTrue(
            $response->headers->get('Access-Control-Allow-Origin') === 'http://localhost:3002' ||
            $response->headers->get('Access-Control-Allow-Origin') === '*'
        );
        $response->assertJsonPath('data.user.username', 'fe_developer');
    }

    public function test_options_preflight_on_products_returns_cors_headers(): void
    {
        $response = $this->call(
            'OPTIONS',
            '/v1/products',
            [],
            [],
            [],
            [
                'HTTP_ORIGIN' => 'http://localhost:3002',
                'HTTP_ACCESS_CONTROL_REQUEST_METHOD' => 'GET',
                'HTTP_ACCESS_CONTROL_REQUEST_HEADERS' => 'x-api-key, id_users',
            ]
        );

        $response->assertStatus(204);
        $this->assertTrue(
            $response->headers->get('Access-Control-Allow-Origin') === 'http://localhost:3002' ||
            $response->headers->get('Access-Control-Allow-Origin') === '*'
        );
    }
}
