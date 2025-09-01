<?php

namespace App\Services\Topic;

use App\Models\Category;
use App\Models\Topic;

class TopicService
{
    // Get all topics for specific category.
    public function index($category_id): array
    {
        if (!Category::find($category_id)) {
            return ['message' => __('msg.category_not_found'), 'code' => 404];
        }
        $topics = Topic::select('id', 'title')
            ->where('category_id', $category_id)
            ->orderBy('title')
            ->get();
        return ['data' => $topics, 'message' => __('msg.topics_retrieved'), 'code' => 200];
    }

    public function search($title): array
    {
        $topics = Topic::select('id', 'title')
            ->where('title', 'LIKE', "%$title%")
            ->orderBy('title')
            ->get();
        if ($topics->isEmpty()) {
            return [
                'data' => [],
                'message' =>  __('msg.no_topics') . $title,
                'suggestions' => Topic::popular()->pluck('title'),
                'code' => 200
            ];
        }
        return ['data' => $topics, 'message' => __('msg.topics_retrieved'), 'code' => 200];
    }
}
