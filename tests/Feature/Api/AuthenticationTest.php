<?php

use Illuminate\Support\Facades\Hash;
use function Pest\Faker\fake;
use App\Models\User;

beforeEach(function () {
    $this->user = User::factory()->create();
});

it('login user', function () {
    $response = $this->postJson("/api/auth/login", [
        'email' => $this->user->email,
        'password' => 'Teste@123',
        'device_name' => 'testing'
    ]);

    $response->assertStatus(200)
        ->assertJsonStructure(['token']);
});

it('logout user', function () {
    $this->user->createToken('testing')->plainTextToken;
    $response = $this->actingAs($this->user, 'web')->postJson('/api/auth/logout');
    $response->assertStatus(200);
});

it('user not authenticated', function () {
    $response = $this->postJson('/api/cards', []);
    $response->assertStatus(401)
        ->assertJsonFragment([
            'message' => 'Unauthenticated.'
        ]);
});
