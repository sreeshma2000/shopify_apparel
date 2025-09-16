@extends('layouts.app')

@section('page-title', 'Overview')

@section('head')
@endsection

@section('content')
<div class="row row-cols-1 row-cols-md-3 g-4">
    <!-- Orders Card -->
    <div class="col-lg-6 col-md-6">
        <a href="{{ route('order.index') }}" class="text-decoration-none text-dark">
            <div class="card widget text-center p-4 h-100">
                <div class="card-header">
                    <h5 class="card-title">Orders</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <i class="bi bi-basket text-warning" style="font-size: 4rem;"></i>
                    </div>
                    <h5 class="mb-2">Orders</h5>
                    <div>
                        <span class="display-5 text-muted">{{ $order_count }}</span>
                    </div>
                </div>
            </div>
        </a>
    </div>

    <!-- Products Card -->
    <div class="col-lg-6 col-md-6">
        <a href="{{ route('product.index') }}" class="text-decoration-none text-dark">
            <div class="card widget text-center p-4 h-100">
                <div class="card-header">
                    <h5 class="card-title">Products</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <i class="bi bi-box-seam text-success" style="font-size: 4rem;"></i>
                    </div>
                    <h5 class="mb-2">Products</h5>
                    <div>
                        <span class="display-5 text-muted">{{ $product_count }}</span>
                    </div>
                </div>
            </div>
        </a>
    </div>
</div>
@endsection

@section('script')
<script src="{{ url("libs/charts/apex/apexcharts.min.js") }}"></script>
<script src="{{ url("libs/slick/slick.min.js") }}"></script>
<script src="{{ url("dist/js/examples/dashboard.js") }}"></script>
@endsection
