<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Client;
use App\Models\Product;
use Illuminate\Http\Request;

class CustomerHomeController extends Controller
{
    public function index(Request $request)
    {
        $client = Client::where('subdomain', 'jaya')
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

        return view('customer.home', compact(
            'client',
            'categories',
            'products'
        ));
    }
}