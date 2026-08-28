<?php

namespace App\Models;

use Carbon\Carbon;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'contact_number',
        'email',
        'password',
        'role',
        'status',
        'date_of_birth',
        'election_id',
        'last_known_election_name',
        'image_path',
        'rejection_message',
        'approved_at',
        'has_voted_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'date_of_birth' => 'date',
            'approved_at' => 'datetime',
            'has_voted_at' => 'datetime',
        ];
    }

    public function election(): BelongsTo
    {
        return $this->belongsTo(Election::class);
    }

    public function adminProfile(): HasOne
    {
        return $this->hasOne(AdminProfile::class);
    }

    public function hostProfile(): HasOne
    {
        return $this->hasOne(HostProfile::class);
    }

    public function hostedElections(): HasMany
    {
        return $this->hasMany(Election::class, 'host_id');
    }

    public function votes(): HasMany
    {
        return $this->hasMany(Vote::class);
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isHost(): bool
    {
        return $this->role === 'host';
    }

    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    public function hasVoted(): bool
    {
        return $this->has_voted_at !== null;
    }

    public function getAgeAttribute(): ?int
    {
        return $this->date_of_birth ? Carbon::parse($this->date_of_birth)->age : null;
    }

    public static function purgeRejectedConflicts(string $contactNumber, string $email): void
    {
        static::query()
            ->where('status', 'rejected')
            ->where(function ($query) use ($contactNumber, $email) {
                $query->where('contact_number', $contactNumber)->orWhere('email', $email);
            })
            ->get()
            ->each(function (self $user) {
                if ($user->image_path) {
                    Storage::disk('public')->delete($user->image_path);
                }

                $user->delete();
            });
    }
}
