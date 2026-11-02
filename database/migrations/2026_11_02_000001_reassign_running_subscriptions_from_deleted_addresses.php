<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * One-time data fix: active/queued subscriptions that still point at an
     * address the customer deleted are moved to the customer's current
     * address (primary first, else newest). Customers with no other address
     * keep the deleted one, which the bill can still show.
     */
    public function up(): void
    {
        $subs = DB::table('user_subcrptions as s')
            ->join('user_addresses as a', 'a.id', '=', 's.user_address_id')
            ->whereNotNull('a.deleted_at')
            ->whereIn('s.status', ['active', 'queued'])
            ->whereNull('s.deleted_at')
            ->select('s.id', 's.user_id')
            ->get();

        foreach ($subs as $sub) {
            $replacementId = DB::table('user_addresses')
                ->where('user_id', $sub->user_id)
                ->whereNull('deleted_at')
                ->orderByDesc('is_primary')
                ->orderByDesc('id')
                ->value('id');

            if ($replacementId) {
                DB::table('user_subcrptions')->where('id', $sub->id)->update(['user_address_id' => $replacementId]);
            }
        }
    }

    public function down(): void
    {
        // Data fix — nothing to undo.
    }
};
