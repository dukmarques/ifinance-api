<?php

use function Pest\Faker\fake;
use function Pest\Laravel\{getJson, actingAs};
use App\Models\User;
use App\Models\Category;
use App\Models\Revenues;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->category = Category::factory()->create([
        'user_id' => $this->user->id
    ]);
});

it('get all revenues', function () {
    Revenues::factory()->count(4)->create([
        'user_id' => $this->user->id,
        'category_id' => $this->category->id,
    ]);

    $date = \Carbon\Carbon::now();

    $response = actingAs($this->user)->getJson("/api/revenues?date={$date->toDateString()}");
    $response->dump()->assertStatus(200)
        ->assertJsonIsArray()
        ->assertJsonCount(4);
});

/*it('get a revenue by id', function () {
    $revenue = Revenues::factory()->create(['user_id' => $this->user->id]);

    $response = actingAs($this->user)->getJson("/api/revenues/{$revenue->id}");
    $response->assertStatus(200)
        ->assertJsonFragment([
            'title' => $revenue->title,
            'amount' => $revenue->amount,
            'receiving_date' => $revenue->receiving_date,
            'user_id' => $this->user->id,
        ]);
});
*/
it('get a non-existent revenue', function () {
    $response = actingAs($this->user)->getJson("/api/revenues/123");
    $response->assertStatus(404)
        ->assertJsonFragment([
            'message' => 'Revenue not found'
        ]);
});
