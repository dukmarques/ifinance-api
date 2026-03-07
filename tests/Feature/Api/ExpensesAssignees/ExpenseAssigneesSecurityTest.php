<?php
use App\Models\Card;
use App\Models\CardExpenses;
use App\Models\Category;
use App\Models\ExpenseAssignees;
use App\Models\Expenses;
use App\Models\User;
use Symfony\Component\HttpFoundation\Response;

use function Pest\Laravel\{actingAs, deleteJson, getJson, putJson};

beforeEach(function () {
    $this->owner = User::factory()->createOne();
    $this->intruder = User::factory()->createOne();
    $this->notFoundMessage = 'Resource not found';
    $this->linkedMessage = 'Responsible cannot be removed because it is linked to expenses.';
    $this->assigneePath = fn (int $id): string => "/api/expense-assignees/{$id}";
    $this->createAssignee = fn (User $user): ExpenseAssignees => ExpenseAssignees::factory()->createOne([
        'user_id' => $user->id,
    ]);
});

dataset('cross_user_assignee_actions', [
    'show action' => fn (int $id) => getJson("/api/expense-assignees/{$id}"),
    'update action' => fn (int $id) => putJson("/api/expense-assignees/{$id}", [
        'name' => 'Updated Name',
    ]),
    'delete action' => fn (int $id) => deleteJson("/api/expense-assignees/{$id}"),
]);

dataset('assignee_links', [
    'linked expense' => function (ExpenseAssignees $assignee, User $owner): void {
        Expenses::factory()->createOne([
            'user_id' => $owner->id,
            'is_owner' => false,
            'assignee_id' => $assignee->id,
        ]);
    },
    'linked card expense' => function (ExpenseAssignees $assignee, User $owner): void {
        $category = Category::factory()->createOne([
            'user_id' => $owner->id,
        ]);
        $card = Card::factory()->createOne([
            'user_id' => $owner->id,
        ]);

        CardExpenses::factory()->createOne([
            'user_id' => $owner->id,
            'is_owner' => false,
            'assignee_id' => $assignee->id,
            'card_id' => $card->id,
            'category_id' => $category->id,
        ]);
    },
]);

describe('ownership boundaries', function () {
    it('returns not found when accessing assignee from another user', function (\Closure $action) {
        $assignee = ($this->createAssignee)($this->intruder);

        actingAs($this->owner);
        $response = $action($assignee->id);

        $response
            ->assertStatus(Response::HTTP_NOT_FOUND)
            ->assertJson([
                'message' => $this->notFoundMessage,
            ]);
    })->with('cross_user_assignee_actions');
});

describe('deletion rules', function () {
    it('blocks deletion when assignee has linked records', function (\Closure $attachLink) {
        $assignee = ($this->createAssignee)($this->owner);
        $attachLink($assignee, $this->owner);

        actingAs($this->owner)
            ->deleteJson(($this->assigneePath)($assignee->id))
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJson([
                'message' => $this->linkedMessage,
            ]);

        expect(ExpenseAssignees::withTrashed()->find($assignee->id)?->trashed())->toBeFalse();
    })->with('assignee_links');

    it('soft deletes assignee without links', function () {
        $assignee = ($this->createAssignee)($this->owner);

        actingAs($this->owner)
            ->deleteJson(($this->assigneePath)($assignee->id))
            ->assertStatus(Response::HTTP_NO_CONTENT);

        expect(ExpenseAssignees::query()->find($assignee->id))->toBeNull()
            ->and(ExpenseAssignees::withTrashed()->find($assignee->id)?->trashed())->toBeTrue();
    });
});
