<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DeletedCandidate extends Model
{
    protected $fillable = [
        'election_archive_id',
        'original_candidate_id',
        'election_name',
        'candidate_name',
        'age',
        'position',
        'email',
        'image_path',
        'vision_path',
        'vote_count',
        'deleted_reason',
        'election_started_at',
        'election_ended_at',
        'deleted_at',
        'restored_at',
    ];

    protected $casts = [
        'age' => 'integer',
        'vote_count' => 'integer',
        'election_started_at' => 'datetime',
        'election_ended_at' => 'datetime',
        'deleted_at' => 'datetime',
        'restored_at' => 'datetime',
    ];

    public function electionArchive(): BelongsTo
    {
        return $this->belongsTo(ElectionArchive::class);
    }
}
