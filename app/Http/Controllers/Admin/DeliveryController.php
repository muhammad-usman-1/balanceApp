<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DeliveryOrder;
use App\Models\SubscriptionDay;
use App\Models\UserSubcrption;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class DeliveryController extends Controller
{
    public function index(Request $request)
    {
        $branchId  = auth()->user()->isBranchUser() ? auth()->user()->branch_id : null;

        $dateInput = $request->filled('date') ? trim($request->input('date')) : null;
        $date = ($dateInput && preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateInput))
            ? Carbon::createFromFormat('Y-m-d', $dateInput)->startOfDay()
            : Carbon::today();
        $dayName = strtolower($date->format('l'));

        Log::info('DeliveryOrders.index', [
            'raw_date_param'  => $request->input('date'),
            'date_input'      => $dateInput,
            'parsed_date'     => $date->toDateTimeString(),
            'day_name'        => $dayName,
            'is_today'        => $date->isToday(),
            'server_timezone' => config('app.timezone'),
            'url'             => $request->fullUrl(),
        ]);

        // Get all active subscriptions that have this day scheduled and are within their date range
        $subscriptionDays = SubscriptionDay::where('day', $dayName)
            ->with([
                'subscription_meals.meal',
                'user_subcrption.user',
                'user_subcrption.address',
                'user_subcrption.branch',
                'user_subcrption.area',
            ])
            ->whereHas('user_subcrption', function ($q) use ($date, $branchId) {
                $q->where('status', 'active')
                  ->whereDate('start_date', '<=', $date)
                  ->whereDate('end_date', '>=', $date)
                  ->when($branchId, fn($q2) => $q2->where('branch_id', $branchId))
                  ->where(function ($q2) use ($date) {
                      $q2->where('is_paused', false)
                         ->orWhere(function ($q3) use ($date) {
                             $q3->where('is_paused', true)
                                ->whereDate('paused_until', '<', $date->toDateString());
                         });
                  })
                  ->whereDoesntHave('pause_requests', function ($q2) use ($date) {
                      $q2->where('status', 'approved')
                         ->whereDate('pause_start_date', '<=', $date->toDateString())
                         ->whereDate('pause_end_date', '>=', $date->toDateString());
                  });
            })
            ->get();

        // Resolve or create a DeliveryOrder for each subscription day
        $deliveryOrders = collect();
        foreach ($subscriptionDays as $subDay) {
            $order = DeliveryOrder::firstOrCreate([
                'user_subcrption_id' => $subDay->user_subcrptions_id,
                'subscription_day_id' => $subDay->id,
                'delivery_date' => $date->toDateString(),
            ], ['status' => 'pending']);

            $order->setRelation('subscriptionDay', $subDay);
            $order->setRelation('subscription', $subDay->user_subcrption);
            $deliveryOrders->push($order);
        }

        Log::info('DeliveryOrders.index result', [
            'orders_count' => $deliveryOrders->count(),
            'date'         => $date->toDateString(),
        ]);

        return view('admin.deliveries.index', compact('deliveryOrders', 'date'));
    }

    public function printNote(DeliveryOrder $deliveryOrder)
    {
        $deliveryOrder->load([
            'subscription.user',
            'subscription.address',
            'subscription.branch',
            'subscription.area',
            'subscriptionDay.subscription_meals.meal',
        ]);

        return view('admin.deliveries.print', compact('deliveryOrder'));
    }

    public function printAll(Request $request)
    {
        $branchId  = auth()->user()->isBranchUser() ? auth()->user()->branch_id : null;

        $dateInput = $request->filled('date') ? trim($request->input('date')) : null;
        $date = ($dateInput && preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateInput))
            ? Carbon::createFromFormat('Y-m-d', $dateInput)->startOfDay()
            : Carbon::today();
        $dayName = strtolower($date->format('l'));

        $subscriptionDays = SubscriptionDay::where('day', $dayName)
            ->with([
                'subscription_meals.meal',
                'user_subcrption.user',
                'user_subcrption.address',
                'user_subcrption.branch',
                'user_subcrption.area',
            ])
            ->whereHas('user_subcrption', function ($q) use ($date, $branchId) {
                $q->where('status', 'active')
                  ->whereDate('start_date', '<=', $date)
                  ->whereDate('end_date', '>=', $date)
                  ->when($branchId, fn($q2) => $q2->where('branch_id', $branchId))
                  ->where(function ($q2) use ($date) {
                      $q2->where('is_paused', false)
                         ->orWhere(function ($q3) use ($date) {
                             $q3->where('is_paused', true)
                                ->whereDate('paused_until', '<', $date->toDateString());
                         });
                  })
                  ->whereDoesntHave('pause_requests', function ($q2) use ($date) {
                      $q2->where('status', 'approved')
                         ->whereDate('pause_start_date', '<=', $date->toDateString())
                         ->whereDate('pause_end_date', '>=', $date->toDateString());
                  });
            })
            ->get();

        $deliveryOrders = collect();
        foreach ($subscriptionDays as $subDay) {
            $order = DeliveryOrder::firstOrCreate([
                'user_subcrption_id' => $subDay->user_subcrptions_id,
                'subscription_day_id' => $subDay->id,
                'delivery_date' => $date->toDateString(),
            ], ['status' => 'pending']);

            $order->setRelation('subscriptionDay', $subDay);
            $order->setRelation('subscription', $subDay->user_subcrption);
            $deliveryOrders->push($order);
        }

        return view('admin.deliveries.print-all', compact('deliveryOrders', 'date'));
    }

    public function updateStatus(Request $request, DeliveryOrder $deliveryOrder)
    {
        $request->validate(['status' => 'required|in:pending,delivered']);
        $deliveryOrder->update(['status' => $request->status]);

        return response()->json(['success' => true]);
    }

    public function makeAllDelivered(Request $request)
    {
        $date = $request->input('date', Carbon::today()->toDateString());

        DeliveryOrder::whereDate('delivery_date', $date)
            ->update(['status' => 'delivered']);

        return response()->json(['success' => true]);
    }
}
