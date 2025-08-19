<?php

namespace App\Livewire;

use App\Models\Comment;
use App\Models\Episode;
use App\Models\Reply;
use Livewire\Component;

class ShowEpisode extends Component
{
    public $episode_id;

    public function mount($episode_id)
    {
        $this->episode_id = $episode_id;
    }

    public function destroy_comment(Comment $comment)
    {
        $comment->delete();
    }

    public function destroy_reply(Reply $reply)
    {
        $reply->delete();
    }

    public function render()
    {
        $episode = Episode::with(['comments' => function($q) {
        $q->with(['replies' => function($q) {
            $q->with(['user' => function($q) {
                $q->withTrashed(); // Only users with trashed
            }]);
        }, 'user' => function($q) {
                $q->withTrashed(); // Only users with trashed
            }]);
        }, 'course' => function($q) {
                $q->withTrashed();
            }])->find($this->episode_id);

        return view('livewire.show_episode', compact('episode'))->layout('components.layouts.header', ['title' => 'Course Management', 'withFooter' => 'true']);
    }
}
