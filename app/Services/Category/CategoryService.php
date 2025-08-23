<?php

namespace App\Services\Category;

use App\Http\Resources\Category\CategoryResource;
use App\Models\Category;

class CategoryService
{
    public function index(): array
    {
        $categories = Category::query()->orderBy('title')->get();
        return ['data' => CategoryResource::collection($categories), 'message' => __('msg.categories_retrieved'), 'code' => 200];
    }

    public function search($title): array
    {
        $categories = Category::query()
            ->where('title', 'LIKE', "%$title%")
            ->orderBy('title', 'asc')
            ->get();
        if ($categories->isEmpty()) {
            return [
                'data' => [],
                'message' => __('msg.no_categories_found') . $title,
                'suggestions' => Category::popular(Category::query())->pluck('title'),
                'code' => 200
            ];
        }
        return ['data' => CategoryResource::collection($categories), 'message' => __('msg.categories_retrieved'), 'code' => 200];
    }
}
