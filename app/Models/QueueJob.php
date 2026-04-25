<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QueueJob extends Model
{
    protected $table = 'jobs';

    public $timestamps = false;

    protected $casts = [
        'payload' => 'array',
        'reserved_at' => 'datetime',
        'available_at' => 'datetime',
        'created_at' => 'datetime',
    ];

    /**
     * Get the display name of the job from the payload.
     */
    public function getDisplayNameAttribute(): string
    {
        return $this->payload['displayName'] ?? 'Unknown Job';
    }
}
