<?php

namespace App\Services\Reply;

use App\Http\Resources\Reply\ReplyResource;
use App\Models\Comment;
use App\Models\Reply;

class ReplyService
{
    // Get all replies for specific comment.
    public function index($comment_id): array
    {
        $comment = Comment::find($comment_id);
        if (is_null($comment)) {
            return ['message' => __('msg.comment_not_found'), 'code' => 404];
        }
        $replies = $comment->replies()->latest('reply_date')->get();
        return ['data' => ReplyResource::collection($replies), 'message' => __('msg.replies_retrieved'), 'code' => 200];
    }

    // Add reply for specific comment.
    public function store($request, $comment_id): array
    {
        if (is_null(Comment::find($comment_id))) {
            return ['message' => __('msg.comment_not_found'), 'code' => 404];
        }

        $user = auth('api')->user();
        if ($user->is_banned)
            return ['message' => __('msg.you_are_baned'), 'code' => 403];

        $reply = Reply::create([
            'comment_id' => $comment_id,
            'user_id' => $user->id,
            'content' => $request['content']
        ]);
        return ['data' => new ReplyResource($reply), 'message' => __('msg.reply_created'), 'code' => 201];
    }

    // Update specific reply.
    public function update($request, $id): array
    {
        $user = auth('api')->user();
        if ($user->is_banned)
            return ['message' => __('msg.you_are_baned'), 'code' => 403];

        $reply = Reply::where([
                'id' => $id,
                'user_id' => $user->id
            ])->first();
        if (is_null($reply)) {
            if (is_null(Reply::find($id))) {
                return ['message' => __('msg.reply_not_found'), 'code' => 404];
            } else {
                return ['message' => __('msg.unauthorized'), 'code' => 401];
            }
        }

        $reply->update([
            'content' => $request['content']
        ]);
        return ['data' => new ReplyResource($reply), 'message' => __('msg.reply_updated'), 'code' => 200];
    }

    // Delete specific reply.
    public function destroy($id): array
    {
        $reply = Reply::where(['id' => $id, 'user_id' => auth('api')->id()])->first();
        if (is_null($reply)) {
            if (is_null(Reply::find($id))) {
                return ['message' => __('msg.reply_not_found'), 'code' => 404];
            } else {
                return ['message' => __('msg.unauthorized'), 'code' => 401];
            }
        }
        $reply->delete();
        return ['message' => __('msg.reply_deleted'), 'code' => 200];
    }

    // Add Like to specific reply.
    public function like($id): array
    {
        $reply = Reply::find($id);

        if (is_null($reply)) {
            return ['message' => __('msg.reply_not_found'), 'code' => 404];
        }

        if ($reply->userlikes()->where('user_id', auth('api')->id())->exists()) {
            $reply->userLikes()->detach(auth('api')->id());
            $reply->update([
                'likes' => $reply->likes - 1
            ]);

            return ['data' => new ReplyResource($reply), 'message' => __('msg.reply_unliked'), 'code' => 200];
        } else {
            $reply->userLikes()->attach(auth('api')->id());
            $reply->update([
                'likes' => $reply->likes + 1
            ]);

            return ['data' => new ReplyResource($reply), 'message' => __('msg.reply_liked'), 'code' => 200];
        }
    }
}
