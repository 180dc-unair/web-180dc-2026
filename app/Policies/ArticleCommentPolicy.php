<?php

namespace App\Policies;

use App\Models\ArticleComment;
use App\Models\User;

class ArticleCommentPolicy
{
    public function update(User $user, ArticleComment $comment): bool
    {
        return $user->role === 'admin' || $comment->user_id === $user->id;
    }

    public function delete(User $user, ArticleComment $comment): bool
    {
        return $this->update($user, $comment);
    }

    public function moderate(User $user, ArticleComment $comment): bool
    {
        return $user->role === 'admin';
    }
}
