@extends('layouts.customer')

@section('content')

@php
// Dummy data matching the image exactly
$dummyProducts = [
    [
        'name' => 'Sony Alpha A7 IV Body Only',
        'category' => 'Kamera Mirrorless',
        'specs' => 'Full-Frame 33MP',
        'chips' => '4K 60p • 10-Bit • Dual Slot',
        'price' => '350.000',
        'stock' => 3,
        'image' => 'https://images.unsplash.com/photo-1516035069371-29a1b244cc32?q=80&w=400&auto=format&fit=crop'
    ],
    [
        'name' => 'Canon EOS R5 Body Only',
        'category' => 'Kamera Mirrorless',
        'specs' => '45MP 8K Raw',
        'chips' => 'IBIS 8-Stop • Dual CFexpress/SD',
        'price' => '450.000',
        'stock' => 2,
        'image' => 'https://images.unsplash.com/photo-1502920917128-1aa500764cbd?q=80&w=400&auto=format&fit=crop'
    ],
    [
        'name' => 'Sony FE 24-70mm f/2.8 GM II',
        'category' => 'Lensa Profesional',
        'specs' => 'E-Mount Zoom',
        'chips' => 'Aperture f/2.8 • Filter 82mm',
        'price' => '200.000',
        'stock' => 4,
        'image' => 'https://images.unsplash.com/photo-1617005082833-1e09210214eb?q=80&w=400&auto=format&fit=crop'
    ],
    [
        'name' => 'Sony FE 70-200mm f/2.8 GM',
        'category' => 'Lensa Profesional',
        'specs' => 'Telephoto Zoom',
        'chips' => 'Optical SteadyShot • Nano AR II',
        'price' => '275.000',
        'stock' => 2,
        'image' => 'https://images.unsplash.com/photo-1621217333550-9831969e6b4e?q=80&w=400&auto=format&fit=crop'
    ],
    [
        'name' => 'Godox SL-150W II Continuous',
        'category' => 'Lighting & Studio',
        'specs' => 'Bowens Mount',
        'chips' => '5600K Daylight • CRI 96+ • Stand',
        'price' => '150.000',
        'stock' => 5,
        'image' => 'https://images.unsplash.com/photo-1589225529342-9908ce910cd5?q=80&w=400&auto=format&fit=crop'
    ],
    [
        'name' => 'DJI RS 3 Pro Gimbal Combo',
        'category' => 'Aksesoris & Stabilizer',
        'specs' => 'Carbon Fiber',
        'chips' => 'Payload 4.5kg • LIDAR Autofocus',
        'price' => '220.000',
        'stock' => 3,
        'image' => 'https://images.unsplash.com/photo-1631481546738-f86d63493e84?q=80&w=400&auto=format&fit=crop'
    ]
];
@endphp

<!-- Main Layout -->
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <livewire:customer.product-catalog :client="$client" :categories="$categories" />
</div>

@endsection