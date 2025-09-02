<?php

use App\Models\CardExpenses;
use App\Models\User;
use App\Models\ExpenseAssignees;
use App\Models\Expenses;
use App\Services\ExpenseAssigneesService;
use Symfony\Component\HttpFoundation\Response;

use function Pest\Faker\fake;
use function Pest\Laravel\{postJson, actingAs, getJson, putJson, deleteJson};

// Mock BaseService para simular o comportamento sem acessar o banco de dados
test()->mock(ExpenseAssigneesService::class, function ($mock) {
    $mock->shouldReceive('index')->andReturn([
        'data' => [
            ['id' => 1, 'name' => 'Assignee 1', 'description' => 'Description 1', 'user_id' => 1, 'expense_count' => 0, 'card_expense_count' => 0],
            ['id' => 2, 'name' => 'Assignee 2', 'description' => 'Description 2', 'user_id' => 1, 'expense_count' => 0, 'card_expense_count' => 0],
            ['id' => 3, 'name' => 'Assignee 3', 'description' => 'Description 3', 'user_id' => 1, 'expense_count' => 0, 'card_expense_count' => 0],
        ]
    ]);

    $mock->shouldReceive('store')->andReturn([
        'id' => 1,
        'name' => 'New Assignee',
        'description' => 'New Description',
        'user_id' => 1,
        'expense_count' => 0,
        'card_expense_count' => 0
    ]);

    $mock->shouldReceive('show')->andReturn([
        'id' => 1,
        'name' => 'Assignee',
        'description' => 'Description',
        'user_id' => 1,
        'expense_count' => 0,
        'card_expense_count' => 0
    ]);

    $mock->shouldReceive('update')->andReturn([
        'id' => 1,
        'name' => 'Updated Assignee',
        'description' => 'Updated Description',
        'user_id' => 1,
        'expense_count' => 0,
        'card_expense_count' => 0
    ]);

    $mock->shouldReceive('destroy')->andReturn(true);
});

beforeEach(function () {
    $this->user = User::factory()->createOne();

    $this->assigneeData = [
        'name' => fake()->name(),
        'description' => fake()->sentence(),
    ];
});

describe('ExpenseAssignees API', function () {
    it('list all expense assignees', function () {
        ExpenseAssignees::factory()->count(3)->create([
            'user_id' => $this->user->id
        ]);

        actingAs($this->user)
            ->getJson('/api/expense-assignees')
            ->assertStatus(Response::HTTP_OK)
            ->assertJsonCount(3, 'data');
    });

    it('list all expense assignees without login', function () {
        ExpenseAssignees::factory()->count(3)->create();

        getJson('/api/expense-assignees')
            ->assertStatus(Response::HTTP_UNAUTHORIZED)
            ->assertJson([
                'message' => 'Unauthenticated.',
            ]);
    });

    it('create an expense assignee', function () {
        actingAs($this->user)
            ->postJson('/api/expense-assignees', $this->assigneeData)
            ->assertStatus(Response::HTTP_CREATED)
            ->assertJson([
                'name' => $this->assigneeData['name'],
                'description' => $this->assigneeData['description'],
            ]);

        expect(ExpenseAssignees::query()->count())->toBe(1);
    });

    it('create an expense assignee without login', function () {
        postJson('/api/expense-assignees', $this->assigneeData)
            ->assertStatus(Response::HTTP_UNAUTHORIZED)
            ->assertJson([
                'message' => 'Unauthenticated.',
            ]);

        expect(ExpenseAssignees::query()->count())->toBe(0);
    });

    it('create an expense assignee without required attributes', function () {
        $this->assigneeData['name'] = null;

        actingAs($this->user)
            ->postJson('/api/expense-assignees', $this->assigneeData)
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJson([
                'message' => 'The name field is required.',
            ]);

        expect(ExpenseAssignees::query()->count())->toBe(0);
    });

    it('show an expense assignee', function () {
        $assignee = ExpenseAssignees::factory()->create([
            'user_id' => $this->user->id
        ]);

        actingAs($this->user)
            ->getJson("/api/expense-assignees/{$assignee->id}")
            ->assertStatus(Response::HTTP_OK)
            ->assertJson([
                'id' => $assignee->id,
                'name' => $assignee->name,
                'description' => $assignee->description,
                'user_id' => $assignee->user_id,
                'expense_count' => 0,
                'card_expense_count' => 0,
            ]);
    });

    it('show an expense assignee that does not exist', function () {
        actingAs($this->user)
            ->getJson("/api/expense-assignees/999")
            ->assertStatus(Response::HTTP_NOT_FOUND)
            ->assertJson([
                'message' => 'Resource not found',
            ]);
    });

    it('update an expense assignee', function () {
        $assignee = ExpenseAssignees::factory()->create([
            'user_id' => $this->user->id
        ]);

        $updatedData = [
            'name' => fake()->name(),
            'description' => fake()->sentence(),
        ];

        actingAs($this->user)
            ->putJson("/api/expense-assignees/{$assignee->id}", $updatedData)
            ->assertStatus(Response::HTTP_OK)
            ->assertJson([
                'name' => $updatedData['name'],
                'description' => $updatedData['description'],
            ]);

        $updatedAssignee = ExpenseAssignees::find($assignee->id);
        expect($updatedAssignee->name)->toBe($updatedData['name']);
        expect($updatedAssignee->description)->toBe($updatedData['description']);
    });

    it('update an expense assignee that does not exist', function () {
        $updatedData = [
            'name' => fake()->name(),
            'description' => fake()->sentence(),
        ];

        actingAs($this->user)
            ->putJson("/api/expense-assignees/999", $updatedData)
            ->assertStatus(Response::HTTP_NOT_FOUND)
            ->assertJson([
                'message' => 'Resource not found',
            ]);
    });

    it('delete an expense assignee', function () {
        $assignee = ExpenseAssignees::factory()->create([
            'user_id' => $this->user->id
        ]);

        actingAs($this->user)
            ->deleteJson("/api/expense-assignees/{$assignee->id}")
            ->assertStatus(Response::HTTP_NO_CONTENT);

        expect(ExpenseAssignees::query()->find($assignee->id))->toBeNull();
    });

    it('delete an expense assignee that does not exist', function () {
        actingAs($this->user)
            ->deleteJson("/api/expense-assignees/999")
            ->assertStatus(Response::HTTP_NOT_FOUND)
            ->assertJson([
                'message' => 'Resource not found',
            ]);
    });
});

describe('ExpenseAssignees relationships', function () {
    it('shows expense count in resource', function () {
        $assignee = ExpenseAssignees::factory()->create([
            'user_id' => $this->user->id
        ]);

        Expenses::factory()->count(3)->create([
            'user_id' => $this->user->id,
            'assignee_id' => $assignee->id
        ]);

        actingAs($this->user)
            ->getJson("/api/expense-assignees/{$assignee->id}")
            ->assertStatus(Response::HTTP_OK)
            ->assertJson([
                'expense_count' => 3,
            ]);
    });

    it('shows card expense count in resource', function () {
        $assignee = ExpenseAssignees::factory()->create([
            'user_id' => $this->user->id
        ]);

        CardExpenses::factory()->count(2)->create([
            'user_id' => $this->user->id,
            'assignee_id' => $assignee->id
        ]);

        actingAs($this->user)
            ->getJson("/api/expense-assignees/{$assignee->id}")
            ->assertStatus(Response::HTTP_OK)
            ->assertJson([
                'card_expense_count' => 2,
            ]);
    });
});
