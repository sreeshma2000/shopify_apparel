@extends('layouts.app')
@section('page-title','Product')
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
                <li class="breadcrumb-item active" aria-current="page">Products</li>
            </ol>
        </nav>
    </div>

    <div class="content">
        <div class="">
            <div class="card">
                <div class="card-body">
                    <div class="d-md-flex gap-4 align-items-center">
                        <div class="d-none d-md-flex">All Products</div>
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
                            <button class="btn btn-primary btn-icon" id="fetch_products_btn">
                                <i class="bi bi-arrow-repeat me-1"></i>Fetch Shopify Products
                            </button>
                             <button class="btn btn-primary btn-icon" id="sync_am_product" data-bs-toggle="modal" data-bs-target="#syncAmProductModal">
                                <i class="bi bi-plus-circle me-1"></i>Sync Am Product
                            </button>
                        </div>
                      

                    </div>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table table-custom table-lg mb-0" id="producttb">
                   <thead>
                        <tr>
                            <th>Image</th>
                            <th>Am Product ID</th>
                            <th>Shopify Product ID</th>
                            <th>title</th>
                            <th>total variants</th>
                            <th>Style Number</th>
                            <th>Price</th>
                            <th>Description</th>
                            <!-- <th>Action</th>\ -->
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- modal -->
<div class="modal fade" id="syncAmProductModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">

      <div class="modal-header">
        <h6 class="modal-title">Sync ApparelMagic Products</h6>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body">
        <form id="syncAmProductForm">

          <div class="form-check mb-3">
            <input class="form-check-input" type="radio" name="sync_option" id="sync_single" value="single" checked>
            <label class="form-check-label" for="sync_single">
              Sync a specific product
            </label>
            <input type="text" class="form-control mt-2" id="shopify_product_id" name="shopify_product_id" placeholder="Shopify Product ID">
          </div>

          <div class="form-check">
            <input class="form-check-input" type="radio" name="sync_option" id="sync_all" value="all">
            <label class="form-check-label" for="sync_all">
              Sync all products
            </label>
          </div>

        </form>
      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-primary" id="syncAmProductSubmit">
          <span class="btn-text">Sync</span>
          <span class="spinner-border spinner-border-sm d-none ms-2"></span>
        </button>
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
var $producttable = $('#producttb').DataTable({
    processing: true,
    serverSide: true,
    ajax: {
        url: '{{ route('product.index') }}',
        data: function (d) {
        }
    },
    columns: [
        { data: 'image', name: 'image' },
        { data: 'product_id', name: 'product_id' },
        { data: 'shopify_product_id', name: 'shopify_product_id' },
         { data: 'title', name: 'title' },
        { data: 'total_variants', name: 'total_variants' },
        { data: 'style_number', name: 'style_number' },
        { data: 'price', name: 'price' },
        { data: 'description', name: 'description' },
    ],

    columnDefs: [{
        defaultContent: '--',
        targets: "_all"
    }]
});

$("#producttb_filter, #producttb_length").hide();

$('#sort').on('change', function () {
    var $column = $(this).find(':selected').data('column');
    var $sort = $(this).find(':selected').data('sort');
    $producttable.order([$column, $sort]).draw();
});

$('#pageLength').on('change', function () {
        $producttable.page.len($(this).val()).draw();
    });

    $('#pageLength').val($producttable.page.len());

    $(document).on("keyup", ".searchInput", function () {
        $producttable.search($(this).val()).draw();
    });
});


$("#fetch_products_btn").on("click", function() {
    let btn = $(this);
    
    // Show loader + disable button
    btn.prop("disabled", true);
    btn.find(".btn-text").text("Fetching...");
    btn.find(".spinner-border").removeClass("d-none");

    $.ajax({
        url: "{{ route('fetch-product') }}",  
        type: "POST",
        data: { _token: "{{ csrf_token() }}" },
        success: function (response) {
            Swal.fire({
                icon: response.status ? 'success' : 'warning',
                title: response.message,
            });
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
            btn.find(".btn-text").text("Fetch Products from Shopify");
            btn.find(".spinner-border").addClass("d-none");
        }
    });
});
 $(document).on('click', '#syncAmProductSubmit', function () {
    var btn = $(this);
     var productId = $('#shopify_product_id').val();
    var sync_all = $('#sync_all').is(':checked') ? 1 : 0;      
    $.ajax({
        url: "{{ route('create-am-products') }}",
         type: 'POST',
        data: {
            product_id: productId,
            sync_all: sync_all,
            _token: $('meta[name="csrf-token"]').attr('content')
        },
        beforeSend: function () {
            btn.prop("disabled", true);
            btn.find(".btn-text").text("Syncing...");
            btn.find(".spinner-border").removeClass("d-none");
        },
         success: function (response) {
            $('#syncAmProductModal').modal('hide');
             Swal.fire({
                icon: 'success',
                title: 'Success!',
                text: response.message || 'Products synced successfully.',
            });
         },
        error: function (xhr) {
                $('#syncAmProductModal').modal('hide');

                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: (xhr.responseJSON && xhr.responseJSON.message) 
                        ? xhr.responseJSON.message 
                        : 'Failed to sync products.',
                });
        },
         complete: function () {
            btn.prop("disabled", false);
            btn.find(".btn-text").text("Sync");
            btn.find(".spinner-border").addClass("d-none");
        }
      })

 });


</script>

@endsection