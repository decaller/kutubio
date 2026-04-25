<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum MetadataRevisionType: string implements HasColor, HasLabel
{
    case RawCapture = 'raw_capture';
    case QrParse = 'qr_parse';
    case LlmDraft = 'llm_draft';
    case MetadataEnrichment = 'metadata_enrichment';
    case HumanReviewed = 'human_reviewed';
    case SystemMerge = 'system_merge';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::RawCapture => 'Raw capture',
            self::QrParse => 'QR parse',
            self::LlmDraft => 'LLM draft',
            self::MetadataEnrichment => 'Metadata enrichment',
            self::HumanReviewed => 'Human reviewed',
            self::SystemMerge => 'System merge',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::HumanReviewed => 'success',
            self::SystemMerge => 'primary',
            self::LlmDraft, self::MetadataEnrichment => 'info',
            self::QrParse => 'warning',
            self::RawCapture => 'gray',
        };
    }
}
