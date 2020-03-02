<?php
declare(strict_types=1);

namespace MetaDevStudio\LaravelComment;

use Illuminate\Database\Eloquent\Relations\MorphMany;
use MetaDevStudio\LaravelComment\Contracts\Commentable;
use MetaDevStudio\LaravelComment\Models\Comment;

/**
 * Trait CanComment
 * @package MetaDevStudio\LaravelComment
 */
trait CanComment
{
    /**
     * @param Commentable $commentable
     * @param string $commentText
     * @param int $rate
     * @return Comment
     */
    public function comment(Commentable $commentable, string $commentText = '', int $rate = 0): Comment
    {
        $commentModel = config('comment.model');

        $comment = new $commentModel([
            'comment' => $commentText,
            'rate' => $commentable->canBeRated() ? $rate : null,
            'approved' => $commentable->mustBeApproved() && !$this->canCommentWithoutApprove() ? false : true,
            'commented_id' => $this->getKey(),
            'commented_type' => get_class(),
        ]);

        $commentable->comments()->save($comment);

        return $comment;
    }

    /**
     * @param Comment $commentable
     * @param string $replyText
     * @return bool
     */
    public function reply(Comment $commentable, string $replyText = '')
    {

        if (!$commentable->canBeAnswered()) {
            return false;
        }

        $commentModel = config('comment.model');

        $comment = new $commentModel([
            'comment' => $replyText,
            'approved' => $commentable->mustBeApproved() && !$this->canCommentWithoutApprove() ? false : true,
            'commented_id' => $this->getKey(),
            'commented_type' => get_class(),
            'commentable_type' => $commentable->getMorphClass(),
            'commentable_id' => $commentable->getKey(),
            'reply_to' => $commentable->getKey()
        ]);

        $comment->save();

        return $comment;
    }

    /**
     * @return bool
     */
    public function canCommentWithoutApprove(): bool
    {
        return false;
    }

    /**
     * @return MorphMany
     */
    public function comments(): MorphMany
    {
        return $this->morphMany(config('comment.model'), 'commented');
    }

    /**
     * @param Commentable $commentable
     * @return bool
     */
    public function hasCommentsOn(Commentable $commentable): bool
    {
        return $this->comments()
            ->where([
                'commentable_id' => $commentable->getKey(),
                'commentable_type' => get_class($commentable),
            ])
            ->exists();
    }
}
