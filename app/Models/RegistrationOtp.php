<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RegistrationOtp extends Model
{
    protected $fillable = [
        'email',
        'name',
        'contact_number',
        'password',
        'date_of_birth',
        'election_id',
        'image_path',
        'otp',
        'attempts',
        'expires_at',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'attempts' => 'integer',
        'expires_at' => 'datetime',
    ];

    public function election(): BelongsTo
    {
        return $this->belongsTo(Election::class);
    }

    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }
}
