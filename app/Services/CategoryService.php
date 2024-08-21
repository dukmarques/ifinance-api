<?php

namespace App\Services;

use App\Http\Resources\CategoryResource;
use App\Models\Category;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;

class CategoryService extends BaseService
{
    public function __construct()
    {
        $this->model = Category::class;
        $this->resourceClass = CategoryResource::class;
    }
}
