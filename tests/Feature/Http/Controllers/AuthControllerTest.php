<?php

use function Pest\Laravel\{getJson, postJson};

beforeEach(function () {
    $this->user = createAuthUser();
});

function getHeaders(): array
{
    $sanctumResponse = getJson('/sanctum/csrf-cookie');
    $xsrfToken = $sanctumResponse->getCookie('XSRF-TOKEN');
    return [
        'X-XSRF-TOKEN' => $xsrfToken,
        'Referer' => env('APP_URL'),
    ];
}

test('loging returns forbidden when already logged in', function () {
    $this->actingAs($this->user);
    $response = postJson('/api/login', []);

    $response->assertRedirect('/home');
});

test('login returns validation errors', function () {
    $response = postJson('/api/login', []);
    $response->assertInvalid(['email', 'password']);

    $response = postJson('/api/login', ['email' => 'bademail@email.com', 'password' => CREDENTIALS['password']]);
    $response->assertInvalid(['email']);

    $response = postJson('/api/login', ['email' => CREDENTIALS['email'], 'password' => 'badpassword']);
    $response->assertInvalid(['email']);
});

test('login returns session state error when not adding XSRF token and Referer headers to stateless request',
    function () {
        $response = postJson('/api/login', CREDENTIALS);

        $response->assertStatus(500);
    });

test('login connects user and returns user when right credentials', function () {
    $response = postJson('/api/login', CREDENTIALS, getHeaders());

    $response->assertOk();
    $response->assertJson([
        'user' => $this->user->toArray(),
    ]);

    $this->assertAuthenticatedAs($this->user);
});

test('user returns forbidden error when not logged in', function () {
    $response = getJson('/api/user/auth');
    $response->assertUnauthorized();
});

test('user returns authenticated user', function () {
    postJson('/api/login', CREDENTIALS, getHeaders());
    $response = getJson('/api/user/auth');

    $response->assertOk();
    $response->assertJson($this->user->toArray());
});

test('logout removes user from session and invalidate auth token', function () {
    $headers = getHeaders();
    $loginResponse = postJson('/api/login', CREDENTIALS, $headers);

    $loginResponse->assertOk();
    $this->assertAuthenticatedAs($this->user);

    $logoutResponse = postJson('/api/logout', [], $headers);
    $logoutResponse->assertNoContent();

    $this->assertGuest('web');
});
