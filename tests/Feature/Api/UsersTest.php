<?php

use function Pest\Faker\fake;
use function Pest\Laravel\{postJson, actingAs};
use App\Models\User;

it('create an user', function () {
    $data = [
        'name' => fake('pt_BR')->name,
        'email' => fake('pt_BR')->safeEmail,
        'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
        'image' => fake()->imageUrl
    ];

    $response = postJson('/api/users', $data);
    $response->assertStatus(201)
        ->assertJsonFragment([
            'name' => $data['name'],
            'email' => $data['email'],
            'image' => $data['image']
        ]);
});

it('show profile authenticated user', function () {
    $user = User::factory()->create();

    $response = actingAs($user)->getJson("/api/users/profile");
    $response->assertStatus(200)
        ->assertJsonFragment([
            'name' => $user->name,
            'email' => $user->email
        ]);
});

it('update an user', function () {
    $user = User::factory()->create();
    $data = [
        'name' => fake('pt_BR')->name,
        'email' => fake()->safeEmail
    ];

    $response = actingAs($user)->putJson("/api/users/profile", $data);
    $response->assertStatus(200)
        ->assertJsonFragment($data);
});
