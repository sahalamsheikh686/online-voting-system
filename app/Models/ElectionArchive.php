<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class ElectionArchive extends Model
{
    protected $fillable = [
        'host_id',
        'election_name',
        'election_title',
        'archive_reason',
        'candidate_count',
        'total_votes',
        'election_started_at',
        'election_ended_at',
        'deleted_at',
        'restored_at',
        'winners',
        'position_results',
    ];

    protected $casts = [
        'candidate_count' => 'integer',
        'total_votes' => 'integer',
        'election_started_at' => 'datetime',
        'election_ended_at' => 'datetime',
        'deleted_at' => 'datetime',
        'restored_at' => 'datetime',
        'winners' => 'array',
        'position_results' => 'array',
    ];

    public function deletedCandidates(): HasMany
    {
        return $this->hasMany(DeletedCandidate::class);
    }

    public function latestDeletedCandidate(): HasOne
    {
        return $this->hasOne(DeletedCandidate::class)->latestOfMany();
    }
}
