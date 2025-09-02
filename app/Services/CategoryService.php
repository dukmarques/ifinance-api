<?php

namespace App\Services;

use App\Models\Category;
use App\Http\Resources\CategoryResource;

class CategoryService extends BaseService
{
    public function __construct()
    {
        $this->model = Category::class;
        $this->resourceClass = CategoryResource::class;
    }
}
