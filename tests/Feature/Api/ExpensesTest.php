<?php

use Carbon\Carbon;
use function Pest\Faker\fake;
use function Pest\Laravel\{getJson, postJson, actingAs};
use App\Models\User;
use App\Models\Category;
use Symfony\Component\HttpFoundation\Response;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->category = Category::factory()->create([
        'user_id' => $this->user->id
    ]);
    $this->date = \Carbon\Carbon::now();
});

it('has api/expenses page', function () {
    $response = $this->get('/api/expenses');

    $response->assertStatus(200);
});
