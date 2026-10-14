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
        // Query aslinya kita nyalakan lagi
        $client = Client::where('subdomain', $subdomain)
            ->where('status', 'aktif')
            ->firstOrFail();

        $categories = Category::where('client_id', $client->id)
            ->orderBy('name')
            ->get();

        session()->put('active_client_id', $client->id);
        session()->put('active_client_subdomain', $client->subdomain);

        return view()->file(
            resource_path('views/customer/customer.home.blade.php'),
            compact('client', 'categories')
        );
    }
}