<?php

namespace App\Services\Comment;

use App\Http\Resources\Comment\CommentResource;
use App\Models\Comment;
use App\Models\Episode;

class CommentService
{
    // Get all comments for specific episode, with a maximum of 15 comments per page.
    public function index($episode_id): array
    {
        $episode = Episode::query()->find($episode_id);
        if (is_null($episode)) {
            return ['message' => __('msg.episode_not_found'), 'code' => 404];
        }
        $comments = $episode->comments()->latest('comment_date')->paginate(15);
        return [
            'data' => CommentResource::collection($comments),
            'meta' => [
                'current_page' => $comments->currentPage(),
                'has_more_pages' => $comments->hasMorePages(),
                'next_page' => $comments->hasMorePages() ? $comments->currentPage() + 1 : null,
            ],
            'message' => __('msg.comments_retrieved'),
            'code' => 200
        ];
    }

    // Load more comments, they are not appearing on the last page.
    public function loadMore($episode_id, $request): array
    {
        $episode = Episode::query()->find($episode_id);
        if (is_null($episode)) {
            return ['message' => __('msg.episode_not_found'), 'code' => 404];
        }
        $comments = $episode->comments()->latest('comment_date')->paginate(15, '*', 'page', $request['page']);
        return [
            'data' => CommentResource::collection($comments),
            'meta' => [
                'current_page' => $comments->currentPage(),
                'has_more_pages' => $comments->hasMorePages(),
                'next_page' => $comments->hasMorePages() ? $comments->currentPage() + 1 : null,
            ],
            'message' => __('msg.comments_retrieved'),
            'code' => 200
        ];
    }

    // Get the authenticated user's comments for a specific episode.
    public function getMyComments($episode_id): array
    {
        if (is_null(Episode::query()->find($episode_id))) {
            return ['message' => __('msg.episode_not_found'), 'code' => 404];
        }
        $comments = Comment::query()
            ->where([
                'episode_id' => $episode_id,
                'user_id' => auth('api')->id()
            ])->latest('comment_date')->get();
        return ['data' => CommentResource::collection($comments), 'message' => __('msg.comments_retrieved'), 'code' => 200];
    }

    // Add comment for specific episode.
    public function store($request, $episode_id): array
    {
        if (is_null(Episode::query()->find($episode_id))) {
            return ['message' => __('msg.episode_not_found'), 'code' => 404];
        }

        $user = auth('api')->user();
        if($user->is_banned)
            return ['message' => __('msg.you_are_baned'), 'code' => 403];

        $comment = Comment::query()->create([
            'episode_id' => $episode_id,
            'user_id' => $user->id,
            'content' => $request['content']
        ]);
        return ['data' => new CommentResource($comment), 'message' => __('msg.comment_created'), 'code' => 201];
    }

    // Update specific comment.
    public function update($request, $id): array
    {
        $user = auth('api')->user();
        if($user->is_banned)
            return ['message' => __('msg.you_are_baned'), 'code' => 403];

        $comment = Comment::query()
            ->where([
                'id' => $id,
                'user_id' => $user->id
            ])->first();
        if (is_null($comment)) {
            if (!Comment::query()->find($id)) {
                return ['message' => __('msg.comment_not_found'), 'code' => 404];
            } else {
                return ['message' => __('msg.unauthorized'), 'code' => 401];
            }
        }

        $comment->update([
            'content' => $request['content']
        ]);
        return ['data' => new CommentResource($comment), 'message' => __('msg.comment_updated'), 'code' => 200];
    }


    // Delete specific comment.
    public function destroy($id): array
    {
        $comment = Comment::query()
            ->where([
                'id' => $id,
                'user_id' => auth('api')->id()
            ])->first();
        if (is_null($comment)) {
            if (!Comment::query()->find($id)) {
                return ['message' => __('msg.comment_not_found'), 'code' => 404];
            } else {
                return ['message' => __('msg.unauthorized'), 'code' => 401];
            }
        }
        $comment->delete();
        return ['message' => __('msg.comment_deleted'), 'code' => 200];
    }

    // Add Like to specific comment.
    public function like($id): array
    {
        $comment = Comment::query()->find($id);
        if (is_null($comment)) {
            return ['message' => __('msg.comment_not_found'), 'code' => 404];
        }

        if ($comment->userLikes()->where('user_id', auth('api')->id())->exists()) {
            $comment->userLikes()->detach(auth('api')->id());
            $comment->decrement('likes');
            return ['data' => new CommentResource($comment), 'message' => __('msg.comment_unliked'), 'code' => 200];
        } else {
            $comment->userLikes()->attach(auth('api')->id());
            $comment->increment('likes');
            return ['data' => new CommentResource($comment), 'message' => __('msg.comment_liked'), 'code' => 200];
        }

    }


}
