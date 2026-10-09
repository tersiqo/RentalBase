@extends('layouts.customer')

@section('content')

<livewire:customer.product-catalog :client="$client" :categories="$categories" />

@endsection