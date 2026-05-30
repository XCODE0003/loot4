<?php

namespace App\Filament\Resources\Orders\Support;

use App\Enums\DeliveryStatus;
use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Filament\Resources\Orders\OrderResource;
use App\Models\Order;
use App\Services\Notifications\OrderNotifier;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Log;

/**
 * Reusable header actions for the Order view/edit pages.
 *
 * NOTE: e-mail / instruction delivery is stubbed for now (logs + UI notification);
 * the real mailer/queue integration lands in a later phase.
 */
class OrderActions
{
    /**
     * @return array<int, Action>
     */
    public static function all(): array
    {
        return [
            self::markPaid(),
            self::markDelivered(),
            self::sendEmail(),
            self::resendInstructions(),
            self::refund(),
            self::duplicate(),
        ];
    }

    /**
     * Manually confirm payment (for cases where the gateway webhook didn't land —
     * e.g. the customer paid in another browser). Marks the order paid and fires
     * the same notifications as the webhook: customer email + Telegram.
     */
    public static function markPaid(): Action
    {
        return Action::make('markPaid')
            ->label('Mark as paid')
            ->icon('heroicon-m-check-badge')
            ->color('success')
            ->requiresConfirmation()
            ->modalDescription('Mark this order as paid, then notify the customer (email) and the orders bot (Telegram).')
            ->visible(fn (Order $record): bool => $record->payment_status !== PaymentStatus::Paid)
            ->action(function (Order $record): void {
                $record->update([
                    'payment_status' => PaymentStatus::Paid,
                    'status' => OrderStatus::Processing,
                ]);
                $record->payments()->update(['status' => PaymentStatus::Paid->value]);

                app(OrderNotifier::class)->paid($record);

                Notification::make()->title('Order marked as paid — customer & Telegram notified')->success()->send();
            });
    }

    public static function markDelivered(): Action
    {
        return Action::make('markDelivered')
            ->label('Mark delivered')
            ->icon('heroicon-m-truck')
            ->color('success')
            ->requiresConfirmation()
            ->visible(fn (Order $record): bool => $record->delivery_status !== DeliveryStatus::Delivered)
            ->action(function (Order $record): void {
                $record->update(['delivery_status' => DeliveryStatus::Delivered]);
                Notification::make()->title('Order marked as delivered')->success()->send();
            });
    }

    public static function sendEmail(): Action
    {
        return Action::make('sendEmail')
            ->label('Send email')
            ->icon('heroicon-m-paper-airplane')
            ->color('gray')
            ->schema([
                TextInput::make('subject')->required()->default('Your Loot4you order'),
                Textarea::make('body')->required()->rows(6),
            ])
            ->action(function (array $data, Order $record): void {
                // Stub: record intent until the mailer integration is wired up.
                Log::info('Order email queued', [
                    'order' => $record->order_number,
                    'to' => $record->email,
                    'subject' => $data['subject'],
                ]);
                Notification::make()->title('Email queued to '.$record->email)->success()->send();
            });
    }

    public static function resendInstructions(): Action
    {
        return Action::make('resendInstructions')
            ->label('Resend instructions')
            ->icon('heroicon-m-arrow-path')
            ->color('gray')
            ->requiresConfirmation()
            ->action(function (Order $record): void {
                Log::info('Order instructions resent', [
                    'order' => $record->order_number,
                    'to' => $record->email,
                ]);
                Notification::make()->title('Delivery instructions resent')->success()->send();
            });
    }

    public static function refund(): Action
    {
        return Action::make('refund')
            ->label('Refund payment')
            ->icon('heroicon-m-arrow-uturn-left')
            ->color('danger')
            ->requiresConfirmation()
            ->modalDescription('Mark this order and its payments as refunded.')
            ->visible(fn (Order $record): bool => $record->payment_status !== PaymentStatus::Refunded)
            ->action(function (Order $record): void {
                $record->update([
                    'status' => OrderStatus::Refunded,
                    'payment_status' => PaymentStatus::Refunded,
                ]);
                $record->payments()->update(['status' => PaymentStatus::Refunded->value]);
                Notification::make()->title('Order refunded')->success()->send();
            });
    }

    public static function duplicate(): Action
    {
        return Action::make('duplicate')
            ->label('Duplicate order')
            ->icon('heroicon-m-document-duplicate')
            ->color('gray')
            ->requiresConfirmation()
            ->action(function (Order $record) {
                $clone = $record->replicate(['order_number']);
                $clone->order_number = null; // regenerated by Order::booted()
                $clone->status = OrderStatus::Pending;
                $clone->payment_status = PaymentStatus::Pending;
                $clone->delivery_status = DeliveryStatus::Pending;
                $clone->save();

                foreach ($record->items as $item) {
                    $copy = $item->replicate();
                    $copy->order_id = $clone->id;
                    $copy->save();
                }

                Notification::make()->title('Order duplicated: '.$clone->order_number)->success()->send();

                return redirect(OrderResource::getUrl('edit', ['record' => $clone]));
            });
    }
}
