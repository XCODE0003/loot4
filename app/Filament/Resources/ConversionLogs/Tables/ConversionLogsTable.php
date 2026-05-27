<?php

namespace App\Filament\Resources\ConversionLogs\Tables;

use App\Enums\ConversionPlatform;
use App\Enums\ConversionStatus;
use App\Jobs\SendConversionEvent;
use App\Models\ConversionLog;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ConversionLogsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('created_at')
                    ->label('Time')
                    ->dateTime('M j, Y H:i:s')
                    ->sortable(),
                TextColumn::make('event')
                    ->badge()
                    ->color('gray')
                    ->searchable(),
                TextColumn::make('platform')
                    ->badge()
                    ->sortable(),
                TextColumn::make('order.order_number')
                    ->label('Order')
                    ->placeholder('—'),
                TextColumn::make('value')
                    ->money(fn (ConversionLog $record): string => $record->currency ?? 'USD')
                    ->placeholder('—')
                    ->alignEnd(),
                TextColumn::make('status')
                    ->label('Sent status')
                    ->badge()
                    ->sortable(),
                TextColumn::make('reason')
                    ->limit(40)
                    ->placeholder('—')
                    ->toggleable(),
                TextColumn::make('url')
                    ->limit(30)
                    ->url(fn (ConversionLog $record): ?string => $record->url)
                    ->openUrlInNewTab()
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('platform')
                    ->options(ConversionPlatform::class),
                SelectFilter::make('status')
                    ->label('Sent status')
                    ->options(ConversionStatus::class),
                SelectFilter::make('event')
                    ->options(fn (): array => ConversionLog::query()
                        ->distinct()
                        ->orderBy('event')
                        ->pluck('event', 'event')
                        ->all()),
                Filter::make('created_at')
                    ->schema([
                        DatePicker::make('from'),
                        DatePicker::make('until'),
                    ])
                    ->query(fn (Builder $query, array $data): Builder => $query
                        ->when($data['from'] ?? null, fn (Builder $q, $d): Builder => $q->whereDate('created_at', '>=', $d))
                        ->when($data['until'] ?? null, fn (Builder $q, $d): Builder => $q->whereDate('created_at', '<=', $d))),
            ])
            ->recordActions([
                ViewAction::make()
                    ->label('View payload'),
                Action::make('retry')
                    ->icon('heroicon-m-arrow-path')
                    ->color('warning')
                    ->visible(fn (ConversionLog $record): bool => $record->status !== ConversionStatus::Sent)
                    ->action(function (ConversionLog $record): void {
                        $record->update(['status' => ConversionStatus::Pending, 'reason' => null]);
                        SendConversionEvent::dispatch($record->id);
                        Notification::make()->title('Conversion event queued for retry')->success()->send();
                    }),
                Action::make('downloadJson')
                    ->label('Download JSON')
                    ->icon('heroicon-m-arrow-down-tray')
                    ->color('gray')
                    ->action(fn (ConversionLog $record): StreamedResponse => self::downloadJson($record)),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('retry')
                        ->label('Retry')
                        ->icon('heroicon-m-arrow-path')
                        ->color('warning')
                        ->requiresConfirmation()
                        ->action(function (Collection $records): void {
                            $records->each(function (ConversionLog $log): void {
                                $log->update(['status' => ConversionStatus::Pending, 'reason' => null]);
                                SendConversionEvent::dispatch($log->id);
                            });
                            Notification::make()->title('Selected events queued for retry')->success()->send();
                        })
                        ->deselectRecordsAfterCompletion(),
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    private static function downloadJson(ConversionLog $record): StreamedResponse
    {
        $payload = [
            'id' => $record->id,
            'platform' => $record->platform->value,
            'event' => $record->event,
            'status' => $record->status->value,
            'value' => $record->value,
            'currency' => $record->currency,
            'order_id' => $record->order_id,
            'url' => $record->url,
            'request_payload' => $record->request_payload,
            'response_payload' => $record->response_payload,
            'created_at' => $record->created_at?->toIso8601String(),
            'sent_at' => $record->sent_at?->toIso8601String(),
        ];

        return response()->streamDownload(
            fn () => print(json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)),
            "conversion-{$record->id}.json",
            ['Content-Type' => 'application/json'],
        );
    }
}
