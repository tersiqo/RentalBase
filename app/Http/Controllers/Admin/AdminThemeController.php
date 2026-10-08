<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminThemeController extends Controller
{
    public function index()
    {
        $client = Auth::user()->client;
        $subscription = $client->activeSubscription;
        
        return view('admin.theme.index', compact('client', 'subscription'));
    }

    public function update(Request $request)
    {
        $client = Auth::user()->client;
        $subscription = $client->activeSubscription;
        $plan = $subscription ? strtolower($subscription->plan_name) : 'starter';

        // Aturan Starter: jika sudah memiliki warna, tolak perubahan.
        if ($plan === 'starter' && !empty($client->theme_color)) {
            return back()->with('error', 'Perubahan tema tidak tersedia pada subscription Starter. Silakan upgrade ke subscription Business atau Professional untuk mengubah tema.');
        }

        $request->validate([
            'theme_color' => ['required', 'string', 'regex:/^#[0-9a-fA-F]{6}$/i'],
        ]);

        $client->update([
            'theme_color' => strtoupper($request->theme_color),
        ]);

        return back()->with('success', 'Tema tampilan berhasil diperbarui.');
    }
}
