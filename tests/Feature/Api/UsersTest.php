<?php

use function Pest\Faker\fake;
use App\Models\User;

it('get all users from api', function () {
    $response = $this->getJson('/api/users');

    $response->assertStatus(403)
        ->assertJsonFragment([
            'message' => 'Unauthorized request'
        ]);
});

it('create an user', function () {
    $data = [
        'name' => fake('pt_BR')->name,
        'email' => fake('pt_BR')->safeEmail,
        'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
        'image' => fake()->imageUrl
    ];

    $response = $this->postJson('/api/users', $data);
    $response->assertStatus(201)
        ->assertJsonFragment([
            'name' => $data['name'],
            'email' => $data['email'],
            'image' => $data['image']
        ]);
});

it('show an user by id', function () {
    $user = User::factory()->create();

    $response = $this->getJson("/api/users/{$user->id}");
    $response->assertStatus(403)
        ->assertJsonFragment([
            'message' => 'Unauthorized request'
        ]);
});

it('update an user', function () {
    $user = User::factory()->create();
    $data = [
        'name' => fake('pt_BR')->name,
        'email' => fake()->safeEmail
    ];

    $response = $this->putJson("/api/users/{$user->id}", $data);
    $response->assertStatus(200)
        ->assertJsonFragment($data);
});

it('deleted a user', function () {
    $user = User::factory()->create();
    $response = $this->deleteJson("/api/users/{$user->id}");
    $response->assertStatus(403)
        ->assertJsonFragment([
            'message' => 'Unauthorized request'
        ]);
});
