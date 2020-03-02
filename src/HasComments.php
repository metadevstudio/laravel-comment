<?php

namespace MetaDevStudio\LaravelComment;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Builder;
use MetaDevStudio\LaravelComment\Models\Comment;

/**
 * Trait HasComments
 * @package MetaDevStudio\LaravelComment
 */
trait HasComments
{
    /**
     * @var null
     */
    protected $sessionDelay = null;

    /**
     * @return MorphMany
     */
    public function comments(): MorphMany
    {
        return $this->morphMany(config('comment.model'), 'commentable')
            ->whereNull('reply_to')
            ->orderBy('created_at', 'DESC');
    }

    /**
     * Return query to comment parent
     * @return Builder
     */
    public function parentComment(): Builder
    {
        return Comment::where('id', $this->reply_to);
    }

    static public function modelComments()
    {
        return Comment::where('commentable_type', 'App\Posts');
    }

    /**
     * @return bool
     */
    public function canBeAnswered(): bool
    {
        if (!empty($this->reply_to)) {
            if (!empty($this->parentComment()->first()->reply_to)) {
                return false;
            } else {
                return true;
            }
        } else {
            return true;
        }
    }

    /**
     * @return bool
     */
    public function canBeRated(): bool
    {
        return false;
    }

    /**
     * @return bool
     */
    public function mustBeApproved(): bool
    {
        return false;
    }

    /**
     * @return string
     */
    public function primaryId(): string
    {
        return (string)$this->getAttribute($this->primaryKey);
    }

    /**
     * @param int $round
     * @return float
     */
    public function averageRate(int $round = 2): float
    {
        if (!$this->canBeRated()) {
            return 0;
        }

        /** @var Builder $rates */
        $rates = $this->comments()->approvedComments();

        if (!$rates->exists()) {
            return 0;
        }

        return round((float)$rates->avg('rate'), $round);
    }

    /**
     * @return int
     */
    public function totalCommentsCount(): int
    {
        if (!$this->mustBeApproved()) {
            return $this->comments()->count();
        }

        return $this->comments()->approvedComments()->count();
    }
}
