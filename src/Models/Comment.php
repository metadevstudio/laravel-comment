<?php

namespace MetaDevStudio\LaravelComment\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Builder;
use MetaDevStudio\LaravelComment\Contracts\Commentable;
use MetaDevStudio\LaravelComment\HasComments;
use Rennokki\Befriended\Contracts\Likeable;
use Rennokki\Befriended\Traits\CanBeLiked;

class Comment extends Model implements Commentable, Likeable
{
    use HasComments, CanBeLiked;

    protected $guarded = ['id'];

    protected $casts = [
        'approved' => 'boolean'
    ];

    public function commentable(): MorphTo
    {
        return $this->morphTo();
    }

    public function commented(): MorphTo
    {
        return $this->morphTo();
    }

    public function scopeApprovedComments(Builder $query): Builder
    {
        return $query->where('approved', true);
    }

    public function approve(): self
    {
        $this->approved = true;
        $this->save();

        return $this;
    }

    public function answers()
    {
        return $this->where('reply_to', $this->id)
            ->where('commentable_type', $this->getMorphClass())
            ->orderBy('created_at', 'ASC');
    }

    /**
     * @return $this|bool
     */
    public function like()
    {
        if ($this->canBeLiked()) {
            $this->increment('likes');
            return $this;
        }
        return false;
    }

    /**
     * @param $delay
     * @return self
     */
    public function delayInSession($delay): self
    {
        if (is_int($delay)) {
            $delay = Carbon::now()->addMinutes($delay);
        }

        $this->sessionDelay = $delay;
        session()->put(get_class($this) . $this->getKey(), $this->sessionDelay);

        return $this;
    }

    /**
     * @return bool
     */
    public function canBeLiked(): bool
    {
        if (!empty(session()->get(get_class($this) . $this->primaryId()))) {
            $dt = Carbon::createFromTimeString(session()->get(get_class($this) . $this->primaryId()));
            if ($dt->lt(now())) {
                return false;
            }
        }
        return true;
    }
}
