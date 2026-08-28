<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HostRegistrationOtp extends Model
{
    protected $fillable = [
        'email',
        'name',
        'contact_number',
        'password',
        'reason_type',
        'reason_message',
        'image_path',
        'otp',
        'attempts',
        'expires_at',
    ];

    protected $casts = [
        'attempts' => 'integer',
        'expires_at' => 'datetime',
    ];

    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }
}
