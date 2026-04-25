<?php

namespace App\Filament\Resources\CaptureSessions\Schemas;

use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class CaptureSessionInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Capture evidence')
                    ->schema([
                        ImageEntry::make('front_image_path')
                            ->label('Front image')
                            ->disk('public')
                            ->placeholder('Not uploaded'),
                        ImageEntry::make('back_image_path')
                            ->label('Back image')
                            ->disk('public')
                            ->placeholder('Not uploaded'),
                        TextEntry::make('decoded_qr_payload')
                            ->copyable()
                            ->placeholder('None')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
                Section::make('Session')
                    ->schema([
                        TextEntry::make('public_id')
                            ->label('Public ID')
                            ->copyable(),
                        TextEntry::make('submittedBy.name')
                            ->label('Submitted by')
                            ->placeholder('System'),
                        TextEntry::make('status')
                            ->badge(),
                        TextEntry::make('quantity')
                            ->numeric(),
                        TextEntry::make('qr_parse_status')
                            ->badge()
                            ->placeholder('Not parsed'),
                        TextEntry::make('metadata_revisions_count')
                            ->label('Revisions')
                            ->numeric(),
                        TextEntry::make('failure_reason')
                            ->placeholder('None')
                            ->columnSpanFull(),
                        TextEntry::make('submitted_at')
                            ->dateTime()
                            ->placeholder('None'),
                        TextEntry::make('processing_started_at')
                            ->dateTime()
                            ->placeholder('None'),
                        TextEntry::make('processing_finished_at')
                            ->dateTime()
                            ->placeholder('None'),
                    ])
                    ->columns(3),
            ]);
    }
}
