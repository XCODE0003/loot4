<?php

namespace App\Http\Controllers;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Models\Order;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Inertia\Inertia;
use Inertia\Response;

class AccountController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user();

        return Inertia::render('loot4/account/Index', [
            'stats' => [
                'orders' => $user->orders()->count(),
                'spent' => (float) $user->orders()->where('payment_status', PaymentStatus::Paid->value)->sum('total'),
                'pending' => $user->orders()->where('status', OrderStatus::Pending->value)->count(),
            ],
            'recentOrders' => $user->orders()->withCount('items')->latest()->take(5)->get()
                ->map(fn (Order $o): array => $this->row($o))->all(),
        ]);
    }

    public function orders(Request $request): Response
    {
        return Inertia::render('loot4/account/Orders', [
            'orders' => $request->user()->orders()->withCount('items')->latest()->get()
                ->map(fn (Order $o): array => $this->row($o))->all(),
        ]);
    }

    public function order(Request $request, Order $order): Response
    {
        abort_unless($order->user_id === $request->user()->id, 403);

        $order->load('items');

        return Inertia::render('loot4/account/OrderShow', [
            'order' => [
                ...$this->row($order),
                'email' => $order->email,
                'subtotal' => (float) $order->subtotal,
                'discount' => (float) $order->discount,
                'deliveryStatus' => $order->delivery_status->value,
                'paymentStatusLabel' => $order->payment_status->getLabel(),
                'statusLabel' => $order->status->getLabel(),
                'items' => $order->items->map(fn ($item): array => [
                    'name' => $item->product_name,
                    'lines' => $item->detailLines(),
                    'qty' => $item->quantity,
                    'price' => (float) $item->price,
                ])->all(),
            ],
        ]);
    }

    public function profile(Request $request): Response
    {
        return Inertia::render('loot4/account/Profile', [
            'user' => [
                'name' => $request->user()->name,
                'email' => $request->user()->email,
            ],
        ]);
    }

    public function updateProfile(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users')->ignore($request->user()->id)],
        ]);

        $request->user()->update($data);

        return back();
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        $request->user()->update(['password' => Hash::make($request->string('password'))]);

        return back();
    }

    /**
     * @return array<string, mixed>
     */
    private function row(Order $order): array
    {
        return [
            'id' => $order->id,
            'number' => $order->order_number,
            'date' => $order->created_at?->format('M j, Y'),
            'status' => $order->status->value,
            'paymentStatus' => $order->payment_status->value,
            'total' => (float) $order->total,
            'currency' => $order->currency,
            'itemsCount' => $order->items_count ?? $order->items()->count(),
        ];
    }
}
