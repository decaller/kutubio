<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FailedJob extends Model
{
    protected $table = 'failed_jobs';

    public $timestamps = false;

    protected $casts = [
        'payload' => 'array',
        'failed_at' => 'datetime',
    ];

    /**
     * Get the display name of the job from the payload.
     */
    public function getDisplayNameAttribute(): string
    {
        return $this->payload['displayName'] ?? 'Unknown Job';
    }
}
