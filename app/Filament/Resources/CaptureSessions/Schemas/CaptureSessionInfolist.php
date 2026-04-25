<?php

namespace App\Filament\Resources\CaptureSessions\Schemas;

use App\Enums\CaptureSessionStatus;
use App\Jobs\ExtractBookDataWithVisionJob;
use App\Jobs\ReadIsbnQrCodeJob;
use App\Jobs\SummarizeCaptureSessionJob;
use Filament\Actions\Action;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Bus;

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

                Section::make('Processing actions')
                    ->schema([
                        Actions::make([
                            Action::make('extractTitle')
                                ->label(fn ($record) => 'Extract Title' . ($record->metadataRevisions()->where('source_stage', 'vision_extraction')->exists() ? ' ✅' : ''))
                                ->icon('heroicon-m-sparkles')
                                ->color(fn ($record) => $record->metadataRevisions()->where('source_stage', 'vision_extraction')->exists() ? 'success' : 'info')
                                ->action(function ($record) {
                                    ExtractBookDataWithVisionJob::dispatch($record);
                                    Notification::make()->title('Title extraction dispatched')->success()->send();
                                })
                                ->disabled(fn ($record) => ! $record->front_image_path || $record->status === CaptureSessionStatus::Processing),

                            Action::make('readIsbnQr')
                                ->label(fn ($record) => 'Read ISBN QR' . ($record->metadataRevisions()->where('source_stage', 'qr_reading')->exists() ? ' ✅' : ''))
                                ->icon('heroicon-m-qr-code')
                                ->color(fn ($record) => $record->metadataRevisions()->where('source_stage', 'qr_reading')->exists() ? 'success' : 'info')
                                ->action(function ($record) {
                                    ReadIsbnQrCodeJob::dispatch($record);
                                    Notification::make()->title('QR reading dispatched')->success()->send();
                                })
                                ->disabled(fn ($record) => ! $record->back_image_path || $record->status === CaptureSessionStatus::Processing),

                            Action::make('summarize')
                                ->label(fn ($record) => 'Final Summary' . ($record->metadataRevisions()->where('source_stage', 'final_summary')->exists() ? ' ✅' : ''))
                                ->icon('heroicon-m-document-text')
                                ->color(fn ($record) => $record->metadataRevisions()->where('source_stage', 'final_summary')->exists() ? 'success' : 'info')
                                ->action(function ($record) {
                                    SummarizeCaptureSessionJob::dispatch($record);
                                    Notification::make()->title('Summary generation dispatched')->success()->send();
                                })
                                ->disabled(fn ($record) => $record->status === CaptureSessionStatus::Processing),

                            Action::make('processAll')
                                ->label('Process All (Chain)')
                                ->icon('heroicon-m-play-circle')
                                ->color('primary')
                                ->action(function ($record) {
                                    $record->update(['status' => CaptureSessionStatus::Processing]);
                                    
                                    Bus::chain([
                                        new ExtractBookDataWithVisionJob($record),
                                        new ReadIsbnQrCodeJob($record),
                                        new SummarizeCaptureSessionJob($record),
                                    ])->dispatch();

                                    Notification::make()->title('Full pipeline dispatched')->success()->send();
                                })
                                ->requiresConfirmation()
                                ->disabled(fn ($record) => ! $record->front_image_path || ! $record->back_image_path || $record->status === CaptureSessionStatus::Processing),
                        ])
                        ->columnSpanFull(),
                        
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('status')
                                    ->badge(),
                                TextEntry::make('qr_parse_status')
                                    ->badge()
                                    ->placeholder('Not parsed'),
                                TextEntry::make('metadata_revisions_count')
                                    ->label('Revisions')
                                    ->numeric(),
                                TextEntry::make('failure_reason')
                                    ->placeholder('None')
                                    ->columnSpanFull()
                                    ->color('danger'),
                            ]),
                    ])
                    ->columns(1),

                Section::make('Session')
                    ->schema([
                        TextEntry::make('public_id')
                            ->label('Public ID')
                            ->copyable(),
                        TextEntry::make('submittedBy.name')
                            ->label('Submitted by')
                            ->placeholder('System'),
                        TextEntry::make('quantity')
                            ->numeric(),
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
