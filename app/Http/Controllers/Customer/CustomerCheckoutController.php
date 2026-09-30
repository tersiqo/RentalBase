<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Product;

class CustomerCheckoutController extends Controller
{
    public function create(string $subdomain, Product $product)
    {
        $client = Client::where('subdomain', $subdomain)
            ->where('status', 'aktif')
            ->firstOrFail();

        // Ensure the product belongs to the client and is active
        if ($product->client_id !== $client->id || $product->status !== 'aktif') {
            abort(404);
        }

        return view()->file(
            resource_path('views/customer/customer.checkout.blade.php'),
            compact('client', 'product')
        );
    }
}
