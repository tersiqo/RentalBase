<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Client;
use App\Models\Product;

class CustomerHomeController extends Controller
{
    public function index(string $subdomain)
    {
        $client = Client::where('subdomain', $subdomain)
            ->where('status', 'aktif')
            ->firstOrFail();

        $categories = Category::where('client_id', $client->id)
            ->orderBy('nama')
            ->get();

        $products = Product::where('client_id', $client->id)
            ->where('status', 'aktif')
            ->with('category')
            ->orderBy('nama')
            ->get();

        return view()->file(
            resource_path('views/customer/customer.home.blade.php'),
            compact(
                'client',
                'categories',
                'products'
            )
        );
    }
}