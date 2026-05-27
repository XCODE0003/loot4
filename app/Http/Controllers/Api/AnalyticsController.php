<?php

namespace App\Http\Controllers\Api;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AnalyticsController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $periodStart = now()->subDays($request->integer('days', 30));

        $paid = Order::query()->where('payment_status', PaymentStatus::Paid->value);
        $revenue = (float) (clone $paid)->where('created_at', '>=', $periodStart)->sum('total');
        $sales = (clone $paid)->where('created_at', '>=', $periodStart)->count();
        $totalOrders = Order::query()->where('created_at', '>=', $periodStart)->count();

        return response()->json([
            'data' => [
                'period_days' => $request->integer('days', 30),
                'revenue' => $revenue,
                'sales' => $sales,
                'orders' => $totalOrders,
                'conversion' => $totalOrders > 0 ? round($sales / $totalOrders * 100, 1) : 0,
                'average_order_value' => $sales > 0 ? round($revenue / $sales, 2) : 0,
                'pending_orders' => Order::query()->where('status', OrderStatus::Pending->value)->count(),
                'new_users' => User::query()->where('created_at', '>=', $periodStart)->count(),
                'products' => Product::query()->count(),
            ],
        ]);
    }
}
