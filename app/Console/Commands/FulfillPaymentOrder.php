<?php

namespace App\Console\Commands;

use App\Models\PaymentOrder;
use App\Models\UserSubcrption;
use App\Services\SubscriptionService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

/**
 * Manually complete a Hesabe order whose money was captured but whose
 * subscription was never created (callback never reached us, or it failed
 * mid-way). Only run this after confirming the payment in the Hesabe dashboard.
 *
 *   php artisan payments:fulfill KNET-1792083260-VBEVAP --reference=<Hesabe paymentId>
 *   php artisan payments:fulfill --list        # show stuck orders
 */
class FulfillPaymentOrder extends Command
{
    protected $signature = 'payments:fulfill
                            {order? : order_token or Hesabe reference (e.g. KNET-1792083260-VBEVAP)}
                            {--reference= : Hesabe paymentId shown in the Hesabe dashboard}
                            {--list : List orders that are pending/unfulfilled}';

    protected $description = 'Create the subscription for a Hesabe order that was paid but never fulfilled';

    public function handle(SubscriptionService $subscriptionService): int
    {
        if ($this->option('list')) {
            $rows = PaymentOrder::whereIn('status', ['pending', 'paid_unfulfilled', 'failed'])
                ->whereNull('subscription_id')
                ->latest()
                ->limit(50)
                ->get(['order_token', 'hesabe_order_reference', 'user_id', 'amount', 'payment_method', 'status', 'is_renewal', 'created_at']);

            $this->table(
                ['order_token', 'reference', 'user', 'amount', 'method', 'status', 'renewal', 'created'],
                $rows->map->only(['order_token', 'hesabe_order_reference', 'user_id', 'amount', 'payment_method', 'status', 'is_renewal', 'created_at'])
            );

            return self::SUCCESS;
        }

        $key = $this->argument('order');
        if (! $key) {
            $this->error('Pass an order_token / reference, or use --list.');
            return self::FAILURE;
        }

        $order = PaymentOrder::where('order_token', $key)
            ->orWhere('hesabe_order_reference', $key)
            ->first();

        if (! $order) {
            $this->error("No payment order found for {$key}.");
            return self::FAILURE;
        }

        if ($order->isPaid()) {
            $this->info("Order already fulfilled (subscription #{$order->subscription_id}).");
            return self::SUCCESS;
        }

        $this->line("Order {$order->hesabe_order_reference} — user #{$order->user_id}, {$order->amount} {$order->currency}, status: {$order->status}");

        if (! $this->confirm('Have you confirmed this payment as successful in the Hesabe dashboard?')) {
            return self::FAILURE;
        }

        $paymentId = $this->option('reference')
            ?: ($order->hesabe_response['paymentId'] ?? $order->hesabe_order_reference);
        $gateway = 'hesabe_' . ($order->payment_method ?? 'knet');

        if ($order->is_renewal) {
            $renewal = $order->subscription_id ? UserSubcrption::find($order->subscription_id) : null;
            if (! $renewal) {
                $this->error('Renewal subscription for this order no longer exists — handle manually.');
                return self::FAILURE;
            }

            $renewal->payment              = 'paid';
            $renewal->payment_reference    = $paymentId;
            $renewal->payment_gateway      = $gateway;
            $renewal->renewal_confirmed_at = now();
            $renewal->save();

            $order->update(['status' => 'paid']);
            $subscriptionId = $renewal->id;
        } else {
            $result = $subscriptionService->createSubscription($order->subscription_data, [
                'status'    => 'paid',
                'reference' => $paymentId,
                'gateway'   => $gateway,
                'currency'  => $order->currency,
                'meta'      => $order->hesabe_response ?? ['manually_fulfilled' => true],
            ]);

            $subscriptionId = $result['user_subscription']->id ?? null;
            $order->update(['status' => 'paid', 'subscription_id' => $subscriptionId]);
        }

        Log::info('payments:fulfill: order manually fulfilled', [
            'order_token'     => $order->order_token,
            'subscription_id' => $subscriptionId,
            'reference'       => $paymentId,
        ]);

        $this->info("Done — subscription #{$subscriptionId} (check its status: it may be 'queued' if the user already has an active plan).");

        return self::SUCCESS;
    }
}
