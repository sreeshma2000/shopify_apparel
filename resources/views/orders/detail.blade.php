@extends('layouts.app')

@section('page-title', 'Order Detail')

@section('content')
<div class="content">
    <!-- Breadcrumb -->
    <div class="mb-4">
        <nav style="--bs-breadcrumb-divider: '>'" aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="{{ route('dashboard') }}">
                        <i class="bi bi-globe2 small me-2"></i> Dashboard
                    </a>
                </li>
                <li class="breadcrumb-item">
                    <a href="{{ route('order.index') }}">Orders</a>
                </li>
                <li class="breadcrumb-item active" aria-current="page">
                    Order #{{ $order->shopify_order_id }}
                </li>
            </ol>
        </nav>
    </div>

    <div class="row">
        <!-- Order Summary -->
        <div class="col-lg-8 col-md-12">
            <div class="card mb-4">
                <div class="card-body">
                    <div class="mb-3 d-flex justify-content-between align-items-center">
                        <span><strong>Order No:</strong> {{ $order->shopify_order_name ?? $order->shopify_order_id }}</span>
                        <span class="badge bg-success">{{ $order->fulfillment_status ?? 'Pending' }}</span>
                    </div>

                    <div class="row mb-4 g-4">
                        <div class="col-md-3 col-sm-6">
                            <p class="fw-bold">Created At</p>
                            {{ \Carbon\Carbon::parse($order->created_at)->format('d/m/Y h:i A') }}
                        </div>
                        <div class="col-md-3 col-sm-6">
                            <p class="fw-bold">Customer</p>
                            {{ $order->name }}
                        </div>
                        <div class="col-md-3 col-sm-6">
                            <p class="fw-bold">Email</p>
                            {{ $order->email }}
                        </div>
                        <div class="col-md-3 col-sm-6">
                            <p class="fw-bold">Phone</p>
                            {{ $order->phone }}
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-body">
                            <h6 class="mb-3">Shipping Address</h6>
                            <div>{{ $order->address_1 }} {{ $order->address_2 }}</div>
                            <div>{{ $order->city }}, {{ $order->state }} {{ $order->postal_code }}</div>
                            <div>{{ $order->country }}</div>
                            <div>{{ $order->phone }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Order Amounts -->
        <div class="col-lg-4 col-md-12 mt-4 mt-lg-0">
            <div class="card mb-4">
                <div class="card-body">
                    <h6 class="card-title mb-3">Payment</h6>
                    <div class="row mb-2">
                        <div class="col-6 text-end">Total Amount:</div>
                        <div class="col-6">${{ number_format($order->shopify_shipping_total, 2) }}</div>
                    </div>
                    <button class="btn btn-primary"></button>
                </div>
            </div>
        </div>
    </div>

    <!-- Order Items -->
    <div class="card widget mt-4">
        <h5 class="card-header">Order Items</h5>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-custom mb-0">
                    <thead>
                        <tr>
                            <th>Description</th>
                            <th>SKU</th>
                            <th>Size</th>
                            <th>Qty</th>
                            <th>Unit Price</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($order->order_items as $item)
                        <tr>
                            <td>{{ $item->description ?? '-' }}</td>
                            <td>{{ $item->shopify_sku ?? $item->sku_alt ?? '-' }}</td>
                            <td>{{ $item->size ?? '-' }}</td>
                            <td>{{ $item->qty }}</td>
                            <td>${{ number_format($item->unit_price, 2) }}</td>
                            <td>${{ number_format($item->qty * $item->unit_price, 2) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr class="fw-bold">
                            <td colspan="5" class="text-end">Grand Total</td>
                            <td>${{ number_format($order->shopify_shipping_total, 2) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
