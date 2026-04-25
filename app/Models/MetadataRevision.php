<?php

namespace App\Models;

use App\Enums\MetadataRevisionType;
use Database\Factories\MetadataRevisionFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['book_id', 'capture_session_id', 'revision_type', 'source_stage', 'source_actor_type', 'source_actor_id', 'confidence_score', 'payload', 'diff_from_previous', 'source_meta'])]
class MetadataRevision extends Model
{
    /** @use HasFactory<MetadataRevisionFactory> */
    use HasFactory;

    /**
     * @return BelongsTo<Book, $this>
     */
    public function book(): BelongsTo
    {
        return $this->belongsTo(Book::class);
    }

    /**
     * @return BelongsTo<CaptureSession, $this>
     */
    public function captureSession(): BelongsTo
    {
        return $this->belongsTo(CaptureSession::class);
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'confidence_score' => 'decimal:4',
            'diff_from_previous' => 'array',
            'payload' => 'array',
            'revision_type' => MetadataRevisionType::class,
            'source_meta' => 'array',
        ];
    }
}
