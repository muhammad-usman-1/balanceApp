<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\AppInquiry;
use Illuminate\Http\Request;

class InquiryController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'subject'     => 'required|string|max:255',
            'description' => 'required|string|max:5000',
        ]);

        $user = auth('sanctum')->user();

        $inquiry = AppInquiry::create([
            'user_id'     => $user?->id,
            'name'        => $user?->name    ?? $request->input('name', 'App User'),
            'email'       => $user?->email   ?? $request->input('email'),
            'mobile'      => $user?->mobile  ?? $request->input('mobile'),
            'subject'     => $validated['subject'],
            'description' => $validated['description'],
            'status'      => 'new',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Your inquiry has been submitted successfully.',
            'data'    => [
                'id'         => $inquiry->id,
                'subject'    => $inquiry->subject,
                'status'     => $inquiry->status,
                'created_at' => $inquiry->created_at->toISOString(),
            ],
        ], 201);
    }

    public function myInquiries(Request $request)
    {
        $user = auth('sanctum')->user();

        $inquiries = AppInquiry::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(fn($i) => [
                'id'          => $i->id,
                'subject'     => $i->subject,
                'description' => $i->description,
                'status'      => $i->status,
                'admin_reply' => $i->admin_reply,
                'replied_at'  => $i->replied_at?->toISOString(),
                'created_at'  => $i->created_at->toISOString(),
            ]);

        return response()->json([
            'success' => true,
            'data'    => $inquiries,
        ]);
    }
}
