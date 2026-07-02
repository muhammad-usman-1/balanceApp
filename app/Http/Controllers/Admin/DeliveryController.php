<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DeliveryOrder;
use App\Models\DeliveryTimeSlot;
use App\Models\SubscriptionDay;
use App\Models\UserSubcrption;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Log;

class DeliveryController extends Controller
{
    public function index(Request $request)
    {
        $branchId  = auth()->user()->isBranchUser() ? auth()->user()->branch_id : null;
        $search    = trim($request->get('search', ''));

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

        $subscriptionDays = SubscriptionDay::where('day', $dayName)
            ->with([
                'subscription_meals.meal',
                'user_subcrption.user',
                'user_subcrption.address',
                'user_subcrption.branch',
                'user_subcrption.area',
            ])
            ->whereHas('user_subcrption', function ($q) use ($date, $branchId, $search) {
                $q->where('status', 'active')
                  ->whereDate('start_date', '<=', $date)
                  ->whereDate('end_date', '>=', $date)
                  ->when($branchId, fn($q2) => $q2->where('branch_id', $branchId))
                  ->when($search, fn($q2) => $q2->whereHas('user', fn($q3) => $q3->where('name', 'like', '%'.$search.'%')))
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

        $allOrders = collect();
        foreach ($subscriptionDays as $subDay) {
            $order = DeliveryOrder::firstOrCreate([
                'user_subcrption_id'  => $subDay->user_subcrptions_id,
                'subscription_day_id' => $subDay->id,
                'delivery_date'       => $date->toDateString(),
            ], ['status' => 'pending']);

            $order->setRelation('subscriptionDay', $subDay);
            $order->setRelation('subscription', $subDay->user_subcrption);
            $allOrders->push($order);
        }

        Log::info('DeliveryOrders.index result', [
            'orders_count' => $allOrders->count(),
            'date'         => $date->toDateString(),
        ]);

        $perPage  = 25;
        $page     = max(1, (int) $request->get('page', 1));
        $total    = $allOrders->count();
        $items    = $allOrders->slice(($page - 1) * $perPage, $perPage)->values();

        $deliveryOrders = new LengthAwarePaginator($items, $total, $perPage, $page, [
            'path'  => $request->url(),
            'query' => $request->query(),
        ]);

        $slotLabels = DeliveryTimeSlot::pluck('label_en', 'value')->all();

        return view('admin.deliveries.index', compact('deliveryOrders', 'date', 'slotLabels', 'search'));
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

        $slotLabels = DeliveryTimeSlot::pluck('label_en', 'value')->all();

        return view('admin.deliveries.print', compact('deliveryOrder', 'slotLabels'));
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

        $slotLabels = DeliveryTimeSlot::pluck('label_en', 'value')->all();

        return view('admin.deliveries.print-all', compact('deliveryOrders', 'date', 'slotLabels'));
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
