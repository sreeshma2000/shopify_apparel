@extends('layouts.app')
@section('page-title','Orders')
@section('head')
<link rel="stylesheet" href="{{ url('libs/dataTable/datatables.min.css') }}" type="text/css">
<link rel="stylesheet" href="{{ url('libs/range-slider/css/ion.rangeSlider.min.css') }}" type="text/css">
<link rel="stylesheet" href="{{ url("libs/toastr.css") }}" />
@endsection
@section('content')
<div class="content ">
    <div class="mb-4">
        <nav style="--bs-breadcrumb-divider: '>';" aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="{{ route('dashboard') }}">
                        <i class="bi bi-globe2 small me-2"></i> Dashboard
                    </a>
                </li>
                <li class="breadcrumb-item active" aria-current="page">Orders</li>
            </ol>
        </nav>
    </div>

    <div class="content">
        <div class="">
            <div class="card">
                <div class="card-body">
                    <div class="d-md-flex gap-4 align-items-center">
                        <div class="d-none d-md-flex">All Orders</div>
                        <div class="d-md-flex gap-4 align-items-center">
                            <form class="mb-3 mb-md-0">
                                <div class="row g-3">
                                    <div class="col-md-7">
                                        <select class="form-select" id="sort">
                                            <option>Sort by</option>
                                            <option data-sort="asc" data-column="1" value="">Name A-z</option>
                                            <option data-sort="desc" data-column="1" value=""> Name Z-a
                                            </option>
                                        </select>
                                    </div>
                                    <div class="col-md-5">
                                        <select class="form-select" id="pageLength">
                                            <option value="10">10</option>
                                            <option value="20">20</option>
                                            <option value="30">30</option>
                                            <option value="40">40</option>
                                            <option value="50">50</option>
                                        </select>
                                    </div>
                                </div>
                            </form>
                        </div>

                        <div class="dropdown ms-auto">
                            <button class="btn btn-primary btn-icon" id="add_shipment_btn">
                                <i class="bi bi-plus-circle me-1"></i> Add Shipment
                            </button>
                            <button class="btn btn-primary btn-icon" id="fetch_orders_btn">
                                <i class="bi bi-arrow-repeat me-1"></i> Fetch Shopify Orders
                            </button>
                             <button class="btn btn-primary btn-icon" id="sync_am_order" data-bs-toggle="modal" data-bs-target="#syncAmOrderModal">
                                <i class="bi bi-plus-circle me-1"></i>Sync Am Order
                            </button>
                            </a>
                        </div>

                    </div>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table table-custom table-lg mb-0" id="ordertb">
                   <thead>
                        <tr>
                            <th>AM Order ID</th>
                            <th>Shopify Order ID</th>
                            <th>Customer Name</th>
                            <th>Am Pickticket ID</th>
                            <th>Am Ship ID</th>
                            <th>Amount</th>
                            <th>Shopify Fulfillment status</th> 
                            <th>Apparel Fulfillment status</th>
                            <th>Status</th>
                            <th>Action</th>                           
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="syncAmOrderModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">

      <div class="modal-header">
        <h6 class="modal-title">Sync ApparelMagic Orders</h6>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body">
        <form id="syncAmProductForm">

          <div class="form-check mb-3">
            <input class="form-check-input" type="radio" name="sync_option" id="sync_single" value="single" checked>
            <label class="form-check-label" for="sync_single">
              Sync a specific Shopify Order
            </label>
            <input type="text" class="form-control mt-2" id="shopify_order_id" name="shopify_order_id" placeholder="Shopify Order ID">
          </div>

          <div class="form-check">
            <input class="form-check-input" type="radio" name="sync_option" id="sync_all" value="all">
            <label class="form-check-label" for="sync_all">
              Sync all Shopify Orders
            </label>
          </div>

        </form>
      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-primary" id="syncAmOrderSubmit">
          <span class="btn-text">Sync</span>
          <span class="spinner-border spinner-border-sm d-none ms-2"></span>
        </button>
      </div>

    </div>
  </div>
</div>

<div class="modal fade" id="fulfilOrderModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">

      <div class="modal-header">
        <h6 class="modal-title">Fulfil Order</h6>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body">
        <form id="fulfilOrderForm">
          <input type="hidden" id="fulfil_order_id" name="order_id">
          <div class="mb-3">
            <label for="tracking_number" class="form-label">Tracking Number</label>
            <input type="text" class="form-control" id="tracking_number" name="tracking_number" placeholder="Enter tracking number" required>
          </div>
        </form>
      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-primary" id="confirmFulfilBtn">
          <span class="btn-text">Confirm</span>
          <span class="spinner-border spinner-border-sm d-none ms-2"></span>
        </button>
      </div>

    </div>
  </div>
</div>


<div class="modal fade" id="addShipmentModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">

      <div class="modal-header">
        <h6 class="modal-title">Add Shipment</h6>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body">
        <form id="addShipmentForm">
          <div class="mb-3">
            <label for="order_ids" class="form-label">AM Order IDs (comma separated)</label>
            <textarea class="form-control" id="order_ids" name="order_ids" rows="3" placeholder="Enter Order IDs"></textarea>
          </div>
        </form>
      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-primary" id="confirmAddShipmentBtn">
          <span class="btn-text">Create Shipment</span>
          <span class="spinner-border spinner-border-sm d-none ms-2"></span>
        </button>
      </div>

    </div>
  </div>
</div>

<div class="modal fade" id="cancelOrderModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Confirm Cancel</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        Are you sure you want to cancel this order?
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        <button type="button" class="btn btn-danger" id="confirmCancelBtn">Yes, Cancel</button>
      </div>
    </div>
  </div>
</div>

@endsection
@section('script')
<script src="{{ url('libs/dataTable/datatables.min.js') }}"></script>
<script src="{{ url('libs/range-slider/js/ion.rangeSlider.min.js') }}"></script>
<script>
$(document).ready(function () {
var $column = $('#sort').find(':selected').data('column');
var $sort = $('#sort').find(':selected').data('sort');
var $ordertable = $('#ordertb').DataTable({
    processing: true,
    serverSide: true,
    ajax: {
        url: '{{ route('order.index') }}',
        data: function (d) {
            
        }
    },
    columns: [
        { data: 'am_order_id', name: 'am_order_id' },
        { data: 'shopify_order_id', name: 'shopify_order_id' },
        { data: 'name', name: 'name' },
        { data: 'pick_ticket_id', name: 'pick_ticket_id' },  
        { data: 'ship_id', name: 'ship_id' },
        { data:'amount',name:'amount'},
        { data:'shopify_fulfillment_status',name:'shopify_fulfillment_status'},
        { data:'fulfillment_status',name:'fulfillment_status'},
        { data: 'status_message', name: 'status_message' },
        { data:'action',name:'action'}

    ],

    columnDefs: [{
        defaultContent: '--',
        targets: "_all"
    }]
});

$("#ordertb_filter, #ordertb_length").hide();

$('#sort').on('change', function () {
    var $column = $(this).find(':selected').data('column');
    var $sort = $(this).find(':selected').data('sort');
    $ordertable.order([$column, $sort]).draw();
});

$('#pageLength').on('change', function () {
        $ordertable.page.len($(this).val()).draw();
    });

    $('#pageLength').val($ordertable.page.len());

    $(document).on("keyup", ".searchInput", function () {
        $ordertable.search($(this).val()).draw();
    });
});

$("#fetch_orders_btn").on("click", function() {
    let btn = $(this);
    
    // Show loader + disable button
    btn.prop("disabled", true);
    btn.find(".btn-text").text("Fetching...");
    btn.find(".spinner-border").removeClass("d-none");

    $.ajax({
        url: "{{ route('fetch-order') }}",  
        type: "POST",
        data: { _token: "{{ csrf_token() }}" },
        success: function (response) {
            Swal.fire({
                icon: response.status ? 'success' : 'warning',
                title: response.status ? 'Success!' : 'Warning!',
                text: response.message
            });
            $("#ordertb").DataTable().ajax.reload();
        },
        error: function (xhr) {
            Swal.fire({
                icon: 'error',
                title: 'Product fetch failed',
                text: xhr.responseJSON?.message || 'Something went wrong.'
            });
        },
        complete: function () {
            // Reset button state
            btn.prop("disabled", false);
            btn.find(".btn-text").text("Fetch Orders from Shopify");
            btn.find(".spinner-border").addClass("d-none");
        }
    });
});

$(document).on('click', '#syncAmOrderSubmit', function () {
    var btn = $(this);
    var orderId = $('#shopify_order_id').val();
    var sync_all = $('#sync_all').is(':checked') ? 1 : 0;      
    $.ajax({

        url: "{{ route('create-am-orders') }}",
        type: 'POST',
        data: {
            order_id: orderId,
            sync_all: sync_all,
            _token: $('meta[name="csrf-token"]').attr('content')
        },
        beforeSend: function () {
            btn.prop("disabled", true);
            btn.find(".btn-text").text("Syncing...");
            btn.find(".spinner-border").removeClass("d-none");
        },
        success: function (response) {
            $('#syncAmOrderModal').modal('hide');
            Swal.fire({
                icon: response.status ? 'success' : 'warning',
                title: response.status ? 'success' : 'warning',
                text: response.message || 'Orders synced successfully.',
            });
            $("#ordertb").DataTable().ajax.reload();
        },
        error: function (xhr) {
            $('#syncAmOrderModal').modal('hide');
            Swal.fire({
                icon: 'error',
                title: 'Error!',
                text: (xhr.responseJSON && xhr.responseJSON.message) 
                    ? xhr.responseJSON.message 
                    : 'Failed to sync orders.',
            });
        },
         complete: function () {
            btn.prop("disabled", false);
            btn.find(".btn-text").text("Sync");
            btn.find(".spinner-border").addClass("d-none");
        }
    });
});


$(document).on("click", "#add_shipment_btn", function() {
    $("#order_ids").val(""); 
    $("#addShipmentModal").modal("show");
});
$(document).on("click", "#confirmAddShipmentBtn", function() {
    let btn = $(this);
    let orderIds = $("#order_ids").val().trim();

    if (!orderIds) {
        Swal.fire({ icon: "warning", title: "Please enter at least one Order ID" });
        return;
    }

    $.ajax({
        url: "{{ route('order.create-shipment') }}",
        type: "POST",
        data: {
            order_ids: orderIds,
            _token: "{{ csrf_token() }}"
        },
        beforeSend: function() {
            btn.prop("disabled", true);
            btn.find(".btn-text").text("Processing...");
            btn.find(".spinner-border").removeClass("d-none");
        },
        success: function(response) {
            $("#addShipmentModal").modal("hide");
            Swal.fire({
                icon: response.status ? 'success' : 'warning',
                title: response.status ? 'Success!' : 'Warning!',
                text: response.message
            });
            $("#ordertb").DataTable().ajax.reload();
        },
        error: function(xhr) {
            Swal.fire({
                icon: "error",
                title: "Error",
                text: xhr.responseJSON?.message || "Something went wrong"
            });
        },
        complete: function() {
            btn.prop("disabled", false);
            btn.find(".btn-text").text("Create Shipment");
            btn.find(".spinner-border").addClass("d-none");
        }
    });
});

$(document).on("click", ".fulfil-order-btn", function () {
    let orderId = $(this).data("id");
    $("#fulfil_order_id").val(orderId);
    $("#tracking_number").val(""); 
    $("#fulfilOrderModal").modal("show");
});

$(document).on("click", "#confirmFulfilBtn", function () {
    let btn = $(this);
    let orderId = $("#fulfil_order_id").val();
    let trackingNumber = $("#tracking_number").val().trim();

    if (!trackingNumber) {
        Swal.fire({ icon: "warning", title: "Tracking number is required" });
        return;
    }

    $.ajax({
        url: "{{ route('order.fulfil') }}",
        type: "POST",
        data: {
            order_id: orderId,
            tracking_number: trackingNumber,
            _token: "{{ csrf_token() }}"
        },
        beforeSend: function () {
            btn.prop("disabled", true);
            btn.find(".btn-text").text("Processing...");
            btn.find(".spinner-border").removeClass("d-none");
        },
        success: function (response) {
            $("#fulfilOrderModal").modal("hide");
            Swal.fire({
                icon: response.status ? 'success' : 'warning',
                title: response.status ? 'Order fulfilled' : 'Something went wrong',
                text: response.message
            });
            $("#ordertb").DataTable().ajax.reload();
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
            btn.find(".btn-text").text("Confirm");
            btn.find(".spinner-border").addClass("d-none");
        }
    });
});

let selectedOrderId = null;

$(document).on("click", ".cancel-order-btn", function () {
    selectedOrderId = $(this).data("id"); 
    $("#cancelOrderModal").modal("show");
});

$("#confirmCancelBtn").on("click", function () {
    if (!selectedOrderId) return;

    $.ajax({
        url: "{{ route('order.cancel') }}", 
        type: "POST",
        data: {
            _token: "{{ csrf_token() }}",
            order_id: selectedOrderId
        },
        success: function (response) {
            $("#cancelOrderModal").modal("hide");
            Swal.fire({
                icon: response.status ? 'success' : 'warning',
                title: response.title ? 'Success!' : 'Something went wrong',
                text: response.message ? response.message : 'Something went wrong.',
            });
            $("#ordertb").DataTable().ajax.reload();
            location.reload();
        },
        error: function () {
            $("#cancelOrderModal").modal("hide");
            Swal.fire({
                icon: 'error',
                title: 'Error!',
                text: 'Error cancelling order!',
            });
        }
    });
});

</script>

@endsection