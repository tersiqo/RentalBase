<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Product;

class CustomerProductController extends Controller
{
    public function show(string $subdomain, int $id)
    {
        $client = Client::where('subdomain', $subdomain)
            ->where('status', 'aktif')
            ->firstOrFail();

        $product = Product::where('client_id', $client->id)
            ->where('id', $id)
            ->where('status', 'aktif')
            ->with(['category', 'units' => function ($query) {
                $query->where('status', 'tersedia');
            }])
            ->firstOrFail();

        session()->put('active_client_id', $client->id);

        // Pass 'client' and 'product' to the view.
        return view('customer.product', compact('client', 'product'));
    }
}
