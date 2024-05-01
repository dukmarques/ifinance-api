<?php

use function Pest\Faker\fake;
use function Pest\Laravel\{getJson, actingAs};
use App\Models\User;
use App\Models\Category;
use App\Models\Revenues;
use App\Models\RevenuesOverrides;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->category = Category::factory()->create([
        'user_id' => $this->user->id
    ]);
    $this->date = \Carbon\Carbon::now();
});

it('get all revenues', function () {
    Revenues::factory()
        ->has(
            RevenuesOverrides::factory()->count(1),
            'overrides'
        )
        ->count(10)
        ->create([
        'user_id' => $this->user->id,
        'category_id' => $this->category->id,
    ]);

    $response = actingAs($this->user)->getJson("/api/revenues?date={$this->date->toDateString()}");
    $response->assertStatus(200)
        ->assertJsonIsArray()
        ->assertJsonCount(10);
});

it('get revenues without override', function () {
    Revenues::factory()
        ->hasOverrides(
            1,
            function () {
                return [
                    'receiving_date' => \Carbon\Carbon::now()->addMonths(2),
                ];
            },
        )
        ->create([
            'user_id' => $this->user->id,
            'category_id' => $this->category->id,
        ]);

    $response = actingAs($this->user)->getJson("/api/revenues?date={$this->date->toDateString()}");
    $response->assertStatus(200)
        ->assertJsonIsArray()
        ->assertJsonCount(0, '0.overrides');
});

it('get revenues with override', function () {
    Revenues::factory()
        ->has(
            RevenuesOverrides::factory()->count(1),
            'overrides'
        )
        ->create([
            'user_id' => $this->user->id,
            'category_id' => $this->category->id,
        ]);

    $response = actingAs($this->user)->getJson("/api/revenues?date={$this->date->toDateString()}");
    $response->assertStatus(200)
        ->assertJsonIsArray();
});

it('get a revenue by id', function () {
    $revenue = Revenues::factory()->create(['user_id' => $this->user->id]);

    $response = actingAs($this->user)->getJson("/api/revenues/{$revenue->id}");
    $response->assertStatus(200)
        ->assertJsonFragment([
            'title' => $revenue->title,
            'amount' => $revenue->amount,
            'user_id' => $this->user->id,
            'description' => $revenue->description,
        ]);
});

it('get a non-existent revenue', function () {
    $response = actingAs($this->user)->getJson("/api/revenues/123");
    $response->assertStatus(404)
        ->assertJsonFragment([
            'message' => 'Revenue not found'
        ]);
});

it('get a revenue with override in date by id', function () {
    $revenue = Revenues::factory()
        ->hasOverrides(1)
        ->create([
            'user_id' => $this->user->id,
            'category_id' => $this->category->id,
        ]);

    $response = actingAs($this->user)->getJson("/api/revenues/{$revenue->id}");
    $response->assertStatus(200)
        ->assertJsonFragment([
            'title' => $revenue->title,
            'amount' => $revenue->amount,
            'user_id' => $this->user->id,
            'description' => $revenue->description,
        ])
        ->assertJsonCount(1, 'overrides');
});

it('get a revenue without override in date by id', function () {
    $revenue = Revenues::factory()
        ->hasOverrides(
            1,
            function () {
                return [
                    'receiving_date' => \Carbon\Carbon::now()->addMonths(2),
                ];
            },
        )
        ->create([
            'user_id' => $this->user->id,
            'category_id' => $this->category->id,
        ]);

    $response = actingAs($this->user)->getJson("/api/revenues/{$revenue->id}");
    $response->assertStatus(200)
        ->assertJsonFragment([
            'title' => $revenue->title,
            'amount' => $revenue->amount,
            'user_id' => $this->user->id,
            'description' => $revenue->description,
        ])
        ->assertJsonCount(0, 'overrides');
});

it('get revenues deprecated with new value', function () {
    $revenue = Revenues::factory()->create([
        'recurrent' => true,
        'receiving_date' => \Carbon\Carbon::now()->subMonths(7),
        'deprecated_date' => \Carbon\Carbon::now()->subMonths(1)
    ]);

    $newRevenue = $revenue->replicate();
    $newRevenue->receiving_date = $this->date;
    $newRevenue->deprecated_date = null;
    $newRevenue->save();

    $response = $this->actingAs($this->user)->getJson("/api/revenues?date={$this->date->toDateString()}");
    $response->assertJsonFragment([
        'title' => $newRevenue->title,
        'amount' => $newRevenue->amount,
        'deprecated_date' => null,
    ]);
});

it('get revenues deprecated without new value', function () {
    $revenue = Revenues::factory()->create([
        'recurrent' => true,
        'receiving_date' => \Carbon\Carbon::now()->subMonths(7),
        'deprecated_date' => \Carbon\Carbon::now()->subMonths(1)
    ]);

    $response = $this->actingAs($this->user)->getJson("/api/revenues?date={$this->date->subMonths(4)->toDateString()}");
    $response->assertJsonFragment([
        'id' => $revenue->id,
        'title' => $revenue->title,
        'amount' => $revenue->amount,
        'description' => $revenue->description,
        'user_id' => $this->user->id,
    ])
    ->assertJsonCount(0, '0.overrides');
});

it('get revenues deprecated without new value and override', function () {
    $revenue = Revenues::factory()
        ->hasOverrides(
            1,
            function () {
                return [
                    'receiving_date' => \Carbon\Carbon::now()->subMonths(7),
                ];
            },
        )
        ->create([
            'receiving_date' => \Carbon\Carbon::now()->subMonths(10),
            'deprecated_date' => \Carbon\Carbon::now()->subMonths(1)
        ]);

    $response = $this->actingAs($this->user)->getJson("/api/revenues?date={$this->date->subMonths(7)->toDateString()}");
    $response->assertJsonFragment([
        'id' => $revenue->id,
        'title' => $revenue->title,
        'amount' => $revenue->amount,
        'user_id' => $revenue->user_id,
    ])->assertJsonCount(1, '0.overrides');
});

it('get revenue deprecated without new value out date', function () {
    $revenue = Revenues::factory()
        ->create([
            'receiving_date' => \Carbon\Carbon::now()->subMonths(10),
            'deprecated_date' => \Carbon\Carbon::now()->subMonths(1)
        ]);

    $response = $this->actingAs($this->user)->getJson("/api/revenues?date={$this->date->toDateString()}");
    $response->assertJsonCount(0);
});

it('get revenues not current', function () {
    $recurrentRevenue = Revenues::factory()
        ->create([
            'recurrent' => true,
            'receiving_date' => \Carbon\Carbon::now()->subMonths(7),
            'deprecated_date' => \Carbon\Carbon::now()->addMonths(4),
        ]);

    $notRecurrentRevenue = Revenues::factory()
        ->create([
            'recurrent' => false,
            'receiving_date' => \Carbon\Carbon::now(),
        ]);

    $response = $this->actingAs($this->user)->getJson("/api/revenues?date={$this->date->toDateString()}");
    $response->assertJsonFragment([
        'title' => $recurrentRevenue->title,
        'amount' => $recurrentRevenue->amount,
        'recurrent' => 1,
    ])
    ->assertJsonFragment([
        'title' => $notRecurrentRevenue->title,
        'amount' => $notRecurrentRevenue->amount,
        'recurrent' => 0,
        'deprecated_date' => null,
    ]);
});
