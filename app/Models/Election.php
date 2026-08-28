<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;

class Election extends Model
{
    protected $fillable = [
        'host_id',
        'name',
        'invite_token',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::creating(function (Election $election) {
            if (! $election->invite_token) {
                do {
                    $token = Str::random(40);
                } while (static::query()->where('invite_token', $token)->exists());

                $election->invite_token = $token;
            }
        });
    }

    public function candidates(): HasMany
    {
        return $this->hasMany(Candidate::class);
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function votes(): HasMany
    {
        return $this->hasMany(Vote::class);
    }

    public function electionSetting(): HasOne
    {
        return $this->hasOne(ElectionSetting::class);
    }

    public function place(): HasOne
    {
        return $this->hasOne(ElectionPlace::class);
    }

    public function host(): BelongsTo
    {
        return $this->belongsTo(User::class, 'host_id');
    }
}
