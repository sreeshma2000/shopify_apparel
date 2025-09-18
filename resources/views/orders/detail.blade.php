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
            <div class="col-lg-8 col-md-12">
                <div class="card mb-4">
                    <div class="card-body">
                        <div class="mb-3 d-flex justify-content-between align-items-center">
                            <span><strong>Order No:</strong>
                                {{ $order->shopify_order_name ?? $order->shopify_order_id }}</span>
                            {{-- <span class="badge bg-success">{{ $order->fulfillment_status ?? 'Pending' }}</span> --}}
                            <span class="badge @if($order->refund_status == 1) bg-success 
                                @elseif($order->returns->isNotEmpty() && $order->returns->first()->credit_memo_id) bg-primary
                                @elseif($order->returns->isNotEmpty()) bg-danger
                                @else bg-secondary @endif">
                                @if($order->refund_status == 1)
                                    Refunded
                                @elseif($order->returns->isNotEmpty() && $order->returns->first()->credit_memo_id)
                                    Returned (Credit Memo Generated)
                                @elseif($order->returns->isNotEmpty())
                                    Already Returned
                                @else
                                    {{ $order->fulfillment_status ?? 'Pending' }}
                                @endif
                            </span>

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
            <div class="col-lg-4 col-md-12 mt-4 mt-lg-0">
                <div class="card mb-4">
                    <div class="card-body">
                        <h6 class="card-title mb-3">Payment</h6>
                        <div class="row mb-2">
                            <div class="col-6 text-end">Total Amount:</div>
                            <div class="col-6">${{ number_format($order->shopify_shipping_total, 2) }}</div>
                        </div>
                        <div class="d-flex justify-content-between">
                                @if ($order->returns->isEmpty())
                                    <button class="btn btn-warning return-btn" data-id="{{ $order->id }}">
                                        Return
                                    </button>
                                @endif

                                @if ($order->returns->isNotEmpty() && $order->returns->first()->credit_memo_id == null)
                                    <button class="btn btn-danger credit-memo-btn" data-id="{{ $order->id }}">
                                        Credit Memo
                                    </button>
                                @endif
                        </div>
                    </div>

                </div>
                <div class="card">
                    <div class="card-body">
                        <h6 class="card-title mb-4">Return and Credit Memo</h6>

                        @if($order->returns->isNotEmpty())
                            @foreach($order->returns as $return)
                                <div class="row justify-content-center mb-3">
                                    <div class="col-6 text-end">Return Id:</div>
                                    <div class="col-6">
                                        <a href="#">#{{ $return->return_authorisation_id }}</a>
                                    </div>
                                </div>
                                <div class="row justify-content-center mb-3">
                                    <div class="col-6 text-end">Credit Memo Id :</div>
                                    <div class="col-6">
                                        {{ $return->credit_memo_id }}
                                    </div>
                                </div>
                                <div class="row justify-content-center mb-3">
                                    <div class="col-6 text-end">Return Created at:</div>
                                    <div class="col-6">
                                        {{ $return->created_at->format('d M Y H:i') }}
                                    </div>
                                </div>
                            @endforeach
                        @else
                            <span class="text-muted">Not Available</span>
                        @endif
                        @if($order->refund_status != 1)
                            <div class="d-flex justify-content-center align-items-center">
                                <button class="btn btn-danger refund-button" data-id="{{ $order->id }}">
                                    Refund
                                </button>
                            </div>
                        @endif
                    </div>

                </div>
            </div>
            <div class="modal fade" id="returnModal" tabindex="-1" aria-labelledby="returnModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="returnModalLabel">Return Order</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <label for="returnReason">Reason for Return</label>
                            <textarea id="returnReason" class="form-control" placeholder="Enter reason"></textarea>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Close</button>
                            <button type="button" id="confirmReturn" class="btn btn-warning">Create Return</button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal fade" id="createCreditMemoModal" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Confirm Create Credit Memo</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            Are you sure you want to Create Credit Memo to this order?
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            <button type="button" class="btn btn-danger" id="confirmCancelBtn">Create</button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal fade" id="createRefundModal" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Confirm Create Refund</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            Are you sure you want to Create Refund to this order?
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            <button type="button" class="btn btn-danger" id="confirmRefundBtn">Create</button>
                        </div>
                    </div>
                </div>
            </div>

@endsection
@section('script')
    <script>
        let selectedOrderId;

        $(document).on('click', '.return-btn', function () {
            selectedOrderId = $(this).data('id');
            $('#returnModal').modal('show');
        });

        $('#confirmReturn').on('click', function () {
            let reason = $('#returnReason').val();

            $.ajax({
                url: '{{ route('orders.create-return') }}',
                type: 'POST',
                data: {
                    order_id: selectedOrderId,
                    reason: reason,
                    _token: '{{ csrf_token() }}'
                },
                success: function (response) {
                    Swal.fire({
                        icon: response.status ? 'success' : 'warning',
                        title: response.title ? 'Success!' : 'Something went wrong',
                        text: response.message ? response.message : 'Return successfully.',
                    });
                    $('#returnModal').modal('hide');
                    $('#returnReason').val('');
                },
                error: function (xhr) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: 'Error creating return: ' + xhr.responseJSON.message,
                    });
                }
            });
        });

        $(document).on("click", ".credit-memo-btn", function () {
            selectedOrderId = $(this).data("id");
            $("#createCreditMemoModal").modal("show");
        });

        $(document).on("click", "#confirmCancelBtn", function () {
            let btn = $(this);

            $.ajax({
                url: "{{ route('orders.create-credit-memo') }}",
                type: "POST",
                data: {
                    order_id: selectedOrderId,
                    _token: "{{ csrf_token() }}"
                },
                beforeSend: function () {
                    btn.prop("disabled", true);
                    btn.text("Processing...");
                },
                success: function (response) {
                    $("#createCreditMemoModal").modal("hide");
                    Swal.fire({
                        icon: response.status ? 'success' : 'warning',
                        title: response.title ? 'Credit Memo Created' : 'Something went wrong',
                        text: response.message ? response.message : 'Credit Memo created successfully.'
                    });
                },
                error: function (xhr) {
                    Swal.fire({
                        icon: "error",
                        title: "Error",
                        text: xhr.responseJSON?.message || "Something went wrong"
                    });
                },
                complete: function () {
                    btn.prop("disabled", false);
                    btn.text("Create");
                }
            });
        });

        $(document).on("click", ".refund-button", function () {
            selectedOrderId = $(this).data("id");
            $("#createRefundModal").modal("show");
        });
        $(document).on("click", "#confirmRefundBtn", function () {
            let btn = $(this);

            $.ajax({
                url: "{{ route('orders.create-refund') }}",
                type: "POST",
                data: {
                    order_id: selectedOrderId,
                    _token: "{{ csrf_token() }}"
                },
                beforeSend: function () {
                    btn.prop("disabled", true);
                    btn.text("Processing...");
                },
                success: function (response) {
                    $("#createRefundModal").modal("hide"); 
                    Swal.fire({
                        icon: response.status ? 'success' : 'warning',
                        title: response.title ? 'Refund Created' : 'Something went wrong',
                        text: response.message ? response.message : 'Refund created successfully.'
                    });
                },

                error: function (xhr) {
                    Swal.fire({
                        icon: "error",
                        title: "Error",
                        text: xhr.responseJSON?.message || "Something went wrong"
                    });
                },
                complete: function () {
                    btn.prop("disabled", false);
                    btn.text("Create");
                }
            });
        });
    </script>
@endsection