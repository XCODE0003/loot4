<?php

namespace App\Filament\Resources\Orders\Tables;

use App\Enums\DeliveryStatus;
use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Models\Currency;
use App\Models\Game;
use App\Models\Order;
use App\Models\Product;
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

class OrdersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('order_number')
                    ->label('Order')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->weight('bold'),
                TextColumn::make('email')
                    ->label('Customer')
                    ->description(fn (Order $record): ?string => $record->user?->name)
                    ->searchable()
                    ->icon('heroicon-m-envelope'),
                TextColumn::make('status')
                    ->badge()
                    ->sortable(),
                TextColumn::make('payment_status')
                    ->label('Payment')
                    ->badge()
                    ->sortable(),
                TextColumn::make('delivery_status')
                    ->label('Delivery')
                    ->badge()
                    ->sortable(),
                TextColumn::make('total')
                    ->money(fn (Order $record): string => $record->currency ?? 'USD')
                    ->sortable()
                    ->alignEnd(),
                TextColumn::make('currency')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('source')
                    ->label('Traffic source')
                    ->badge()
                    ->color('gray')
                    ->placeholder('—')
                    ->toggleable(),
                TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime('M j, Y H:i')
                    ->since()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options(self::enumOptions(OrderStatus::cases())),
                SelectFilter::make('payment_status')
                    ->label('Payment status')
                    ->options(self::enumOptions(PaymentStatus::cases())),
                SelectFilter::make('delivery_status')
                    ->label('Delivery status')
                    ->options(self::enumOptions(DeliveryStatus::cases())),
                SelectFilter::make('currency')
                    ->options(fn (): array => Currency::query()->pluck('code', 'code')->all()),
                SelectFilter::make('source')
                    ->label('Traffic source')
                    ->options(fn (): array => Order::query()
                        ->whereNotNull('source')
                        ->distinct()
                        ->orderBy('source')
                        ->pluck('source', 'source')
                        ->all()),
                SelectFilter::make('game')
                    ->options(fn (): array => Game::query()->orderBy('name')->pluck('name', 'id')->all())
                    ->query(fn (Builder $query, array $data): Builder => $query->when(
                        $data['value'] ?? null,
                        fn (Builder $q, $gameId): Builder => $q->whereHas(
                            'items.product',
                            fn (Builder $sub): Builder => $sub->where('game_id', $gameId),
                        ),
                    )),
                SelectFilter::make('product')
                    ->options(fn (): array => Product::query()->orderBy('name')->pluck('name', 'id')->all())
                    ->query(fn (Builder $query, array $data): Builder => $query->when(
                        $data['value'] ?? null,
                        fn (Builder $q, $productId): Builder => $q->whereHas(
                            'items',
                            fn (Builder $sub): Builder => $sub->where('product_id', $productId),
                        ),
                    )),
                Filter::make('created_at')
                    ->schema([
                        DatePicker::make('from')->label('Created from'),
                        DatePicker::make('until')->label('Created until'),
                    ])
                    ->query(fn (Builder $query, array $data): Builder => $query
                        ->when($data['from'] ?? null, fn (Builder $q, $date): Builder => $q->whereDate('created_at', '>=', $date))
                        ->when($data['until'] ?? null, fn (Builder $q, $date): Builder => $q->whereDate('created_at', '<=', $date)))
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];
                        if ($data['from'] ?? null) {
                            $indicators[] = 'From '.$data['from'];
                        }
                        if ($data['until'] ?? null) {
                            $indicators[] = 'Until '.$data['until'];
                        }

                        return $indicators;
                    }),
            ])
            ->recordActions([
                ViewAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('markDelivered')
                        ->label('Mark as delivered')
                        ->icon('heroicon-m-truck')
                        ->color('success')
                        ->requiresConfirmation()
                        ->action(fn (Collection $records) => $records->each->update(['delivery_status' => DeliveryStatus::Delivered]))
                        ->deselectRecordsAfterCompletion()
                        ->after(fn () => Notification::make()->title('Orders marked as delivered')->success()->send()),
                    BulkAction::make('markPending')
                        ->label('Mark as pending')
                        ->icon('heroicon-m-clock')
                        ->color('warning')
                        ->requiresConfirmation()
                        ->action(fn (Collection $records) => $records->each->update(['delivery_status' => DeliveryStatus::Pending]))
                        ->deselectRecordsAfterCompletion(),
                    BulkAction::make('refund')
                        ->label('Refund')
                        ->icon('heroicon-m-arrow-uturn-left')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->modalDescription('This will mark the selected orders and their payments as refunded.')
                        ->action(fn (Collection $records) => $records->each(function (Order $order): void {
                            $order->update([
                                'status' => OrderStatus::Refunded,
                                'payment_status' => PaymentStatus::Refunded,
                            ]);
                            $order->payments()->update(['status' => PaymentStatus::Refunded->value]);
                        }))
                        ->deselectRecordsAfterCompletion()
                        ->after(fn () => Notification::make()->title('Orders refunded')->success()->send()),
                    BulkAction::make('exportCsv')
                        ->label('Export CSV')
                        ->icon('heroicon-m-arrow-down-tray')
                        ->color('gray')
                        ->action(fn (Collection $records): StreamedResponse => self::exportCsv($records))
                        ->deselectRecordsAfterCompletion(),
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    /**
     * Map enum cases to a [value => label] options array.
     *
     * @param  array<int, \App\Enums\OrderStatus|\App\Enums\PaymentStatus|\App\Enums\DeliveryStatus>  $cases
     * @return array<string, string>
     */
    private static function enumOptions(array $cases): array
    {
        $options = [];

        foreach ($cases as $case) {
            $options[$case->value] = $case->getLabel();
        }

        return $options;
    }

    /**
     * Stream the selected orders as a CSV download.
     *
     * @param  Collection<int, Order>  $records
     */
    private static function exportCsv(Collection $records): StreamedResponse
    {
        $filename = 'orders-'.now()->format('Ymd-His').'.csv';

        return response()->streamDownload(function () use ($records): void {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Order', 'Email', 'Status', 'Payment', 'Delivery', 'Subtotal', 'Discount', 'Total', 'Currency', 'Source', 'Created']);

            foreach ($records as $order) {
                fputcsv($handle, [
                    $order->order_number,
                    $order->email,
                    $order->status->value,
                    $order->payment_status->value,
                    $order->delivery_status->value,
                    $order->subtotal,
                    $order->discount,
                    $order->total,
                    $order->currency,
                    $order->source,
                    $order->created_at?->toDateTimeString(),
                ]);
            }

            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv']);
    }
}
