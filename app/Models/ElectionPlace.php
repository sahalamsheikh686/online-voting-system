<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ElectionPlace extends Model
{
    protected $fillable = [
        'election_id',
        'name',
    ];

    public function election(): BelongsTo
    {
        return $this->belongsTo(Election::class);
    }
}
