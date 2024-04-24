<?php

namespace App\Services;

use App\Models\Category;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;

class CategoryService
{
    public function index(): Collection {
        return Category::withCount(['revenues', 'expenses'])
            ->get();
    }

    public function show(string $id): Category|null {
        return Category::find($id);
    }

    public function store($data): Category|null {
        $data['user_id'] = Auth::id();
        return Category::create($data);
    }

    public function update($id, $data): Category|null {
        $category = Category::find($id);

        if (!$category) {
            return null;
        }

        $category->update($data);
        return $category;
    }

    public function destroy($id): bool {
        return Category::destroy($id);
    }
}
