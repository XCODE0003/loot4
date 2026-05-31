<?php

namespace App\Filament\Resources\Orders\Schemas;

use App\Models\Order;
use App\Services\Geo\IpCountry;
use Filament\Infolists\Components\KeyValueEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class OrderInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Order')
                    ->icon('heroicon-m-rectangle-stack')
                    ->columns(4)
                    ->schema([
                        TextEntry::make('order_number')->label('Order #')->copyable()->weight('bold'),
                        TextEntry::make('status')->badge(),
                        TextEntry::make('payment_status')->label('Payment')->badge(),
                        TextEntry::make('delivery_status')->label('Delivery')->badge(),
                        TextEntry::make('subtotal')->money(fn (Order $r): string => $r->currency ?? 'USD'),
                        TextEntry::make('discount')->money(fn (Order $r): string => $r->currency ?? 'USD'),
                        TextEntry::make('total')->money(fn (Order $r): string => $r->currency ?? 'USD')->weight('bold'),
                        TextEntry::make('created_at')->label('Placed')->dateTime('M j, Y H:i'),
                    ]),

                Section::make('Customer Information')
                    ->icon('heroicon-m-user')
                    ->columns(3)
                    ->schema([
                        TextEntry::make('email')->copyable()->icon('heroicon-m-envelope'),
                        TextEntry::make('user.name')->label('User')->placeholder('Guest'),
                        TextEntry::make('user_id')->label('User ID')->placeholder('—'),
                        TextEntry::make('ip')->label('IP')->placeholder('—'),
                        TextEntry::make('country')
                            ->state(fn ($record): ?string => $record->country ?: app(IpCountry::class)->lookup($record->ip))
                            ->placeholder('—'),
                        TextEntry::make('user.created_at')->label('Registration date')->dateTime()->placeholder('—'),
                    ]),

                Section::make('Payment Information')
                    ->icon('heroicon-m-credit-card')
                    ->columns(3)
                    ->schema([
                        TextEntry::make('currency'),
                        TextEntry::make('discount_code')->placeholder('—'),
                        TextEntry::make('payment_status')->label('Payment status')->badge(),
                        RepeatableEntry::make('payments')
                            ->label('Transactions')
                            ->columnSpanFull()
                            ->columns(4)
                            ->schema([
                                TextEntry::make('method'),
                                TextEntry::make('transaction_id')->label('Transaction ID')->copyable()->placeholder('—'),
                                TextEntry::make('amount')->money(fn ($record): string => $record->currency ?? 'USD'),
                                TextEntry::make('status')->badge(),
                            ]),
                    ]),

                Section::make('Attribution / Traffic Source')
                    ->icon('heroicon-m-chart-bar')
                    ->collapsible()
                    ->columns(3)
                    ->schema([
                        TextEntry::make('source')->placeholder('—'),
                        TextEntry::make('medium')->placeholder('—'),
                        TextEntry::make('campaign')->placeholder('—'),
                        TextEntry::make('content')->placeholder('—'),
                        TextEntry::make('term')->placeholder('—'),
                        TextEntry::make('landing_page')->label('Landing page')->placeholder('—'),
                        TextEntry::make('fbclid')->label('Facebook Click ID')->placeholder('—'),
                        TextEntry::make('ttclid')->label('TikTok Click ID')->placeholder('—'),
                        TextEntry::make('first_visit_at')->label('First visit')->dateTime()->placeholder('—'),
                    ]),

                Section::make('Items')
                    ->icon('heroicon-m-shopping-cart')
                    ->schema([
                        RepeatableEntry::make('items')
                            ->hiddenLabel()
                            ->columns(4)
                            ->schema([
                                TextEntry::make('product_name')->label('Product')->weight('bold'),
                                TextEntry::make('options')
                                    ->state(fn ($record): ?string => is_array($record->form_data) ? ($record->form_data['option'] ?? null) : null)
                                    ->placeholder('—'),
                                TextEntry::make('quantity')->label('Qty'),
                                TextEntry::make('price')->money(fn ($record): string => $record->order?->currency ?? 'USD'),
                                TextEntry::make('status')->badge(),
                            ]),
                    ]),

                Section::make('Dynamic Form Data')
                    ->icon('heroicon-m-adjustments-horizontal')
                    ->collapsible()
                    ->schema([
                        KeyValueEntry::make('form_data')
                            ->hiddenLabel()
                            ->keyLabel('Field')
                            ->valueLabel('Value')
                            ->placeholder('No custom fields'),
                        TextEntry::make('notes')->placeholder('—')->columnSpanFull(),
                    ]),
            ]);
    }
}
