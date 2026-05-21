<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DeliveryOrder;
use App\Models\SubscriptionDay;
use App\Models\UserSubcrption;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DeliveryController extends Controller
{
    public function index(Request $request)
    {
        $date = $request->input('date') ? Carbon::parse($request->input('date')) : Carbon::today();
        $dayName = strtolower($date->format('l'));

        // Get all active subscriptions that have this day scheduled and are within their date range
        $subscriptionDays = SubscriptionDay::where('day', $dayName)
            ->with([
                'subscription_meals.meal',
                'user_subcrption.user',
                'user_subcrption.address',
                'user_subcrption.branch',
                'user_subcrption.area',
            ])
            ->whereHas('user_subcrption', function ($q) use ($date) {
                $q->where('status', 'active')
                  ->where('is_paused', false)
                  ->whereDate('start_date', '<=', $date)
                  ->whereDate('end_date', '>=', $date);
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
        $date = $request->input('date') ? Carbon::parse($request->input('date')) : Carbon::today();
        $dayName = strtolower($date->format('l'));

        $subscriptionDays = SubscriptionDay::where('day', $dayName)
            ->with([
                'subscription_meals.meal',
                'user_subcrption.user',
                'user_subcrption.address',
                'user_subcrption.branch',
                'user_subcrption.area',
            ])
            ->whereHas('user_subcrption', function ($q) use ($date) {
                $q->where('status', 'active')
                  ->where('is_paused', false)
                  ->whereDate('start_date', '<=', $date)
                  ->whereDate('end_date', '>=', $date);
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
