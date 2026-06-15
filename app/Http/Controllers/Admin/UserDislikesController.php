<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class UserDislikesController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->get('search');

        $users = User::whereDoesntHave('roles')
            ->whereNull('branch_id')
            ->whereNotNull('dislikes')
            ->where('dislikes', '!=', '[]')
            ->when($search, function ($q) use ($search) {
                $q->where(function ($q2) use ($search) {
                    $q2->where('name', 'like', "%{$search}%")
                       ->orWhere('mobile', 'like', "%{$search}%")
                       ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->orderByDesc('id')
            ->paginate(25)
            ->withQueryString();

        return view('admin.user-dislikes.index', compact('users', 'search'));
    }
}
