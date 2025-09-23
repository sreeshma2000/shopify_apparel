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
                <li class="breadcrumb-item active" aria-current="page">Inventory Reports</li>
            </ol>
        </nav>
    </div>

    <div class="content">
        <div class="">
            <div class="card">
                <div class="card-body">
                    <div class="d-md-flex gap-4 align-items-center">
                        <div class="d-none d-md-flex">All Inventory Reports</div>
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
                            <button class="btn btn-primary btn-icon" id="syncBtn">
                                <i class="bi bi-arrow-repeat me-1"></i>Sync Inventories
                            </button>
                             <button class="btn btn-primary btn-icon" id="exportBtn">
                                <i class="bi bi-plus-circle me-1"></i>Export
                            </button>
                        </div>
                      

                    </div>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table table-custom table-lg mb-0" id="inventorytb">
                  <thead>
                    <tr>
                        <th>AM SKU ID</th>
                        <th>Shopify SKU</th>
                        <th>Product Name</th>
                        <th>AM Qty Available</th>
                        <th>Shopify Qty Available</th>
                        <th>Shopify Barcode</th>
                        <th>AM UPC</th>
                    </tr>
                </thead>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- modal -->
<div class="modal fade" id="syncAmInventoryModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">

      <div class="modal-header">
        <h6 class="modal-title">Sync ApparelMagic Products</h6>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body">
        <form id="syncAmProductForm">

          <div class="form-check">
            <input class="form-check-input" type="radio" name="sync_option" id="sync_all" value="all">
            <label class="form-check-label" for="sync_all">
              Sync all inventories
            </label>
          </div>

        </form>
      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-primary" id="syncAmInventorySubmit">
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
var $inventorytable = $('#inventorytb').DataTable({
    processing: true,
    serverSide: true,
    ajax: {
        url: '{{ route('report.index') }}',
        data: function (d) {
        }
    },
    columns: [
        { data: 'am_sku_id', name: 'am_sku_id' },
        { data: 'shopify_sku_id', name: 'shopify_sku_id' },
        { data: 'product_name', name: 'product_name' },
        { data: 'am_quantity_available', name: 'am_quantity_available' },
        { data: 'shopify_quantity_available', name: 'shopify_quantity_available' },
        { data: 'shopify_barcode', name: 'shopify_barcode' },
        { data: 'am_upc_display', name: 'am_upc_display' }
    ],

    columnDefs: [{
        defaultContent: '--',
        targets: "_all"
    }]
});

$("#inventorytb_filter, #inventorytb_length").hide();

$('#sort').on('change', function () {
    var $column = $(this).find(':selected').data('column');
    var $sort = $(this).find(':selected').data('sort');
    $inventorytable.order([$column, $sort]).draw();
});

$('#pageLength').on('change', function () {
        $inventorytable.page.len($(this).val()).draw();
    });

    $('#pageLength').val($inventorytable.page.len());

    $(document).on("keyup", ".searchInput", function () {
        $inventorytable.search($(this).val()).draw();
    });
});
$(document).on('click', '#syncBtn', function() {
    $('#syncAmInventoryModal').modal('show');
});

 $(document).on('click', '#syncAmInventorySubmit', function () {
    var btn = $(this);
    var sync_all = $('#sync_all').is(':checked') ? 1 : 0; 

    $.ajax({
        url: "{{ route('inventory.sync') }}",
         type: 'POST',
        data: {
            sync_all: sync_all,
            _token: $('meta[name="csrf-token"]').attr('content')
        },
        beforeSend: function () {
            btn.prop("disabled", true);
            btn.find(".btn-text").text("Syncing...");
            btn.find(".spinner-border").removeClass("d-none");
        },
         success: function (response) {
            $('#syncAmInventoryModal').modal('hide');
            Swal.fire({
                icon: response.success ? 'success' : 'warning',
                title: response.success ? 'Success' : 'Warning',
                text: response.message || 'Products synced successfully.',
            });
         },
        error: function (xhr) {
                $('#syncAmInventoryModal').modal('hide');

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

 $('#exportBtn').click(function() {
    const $btn = $(this);
    $btn.prop('disabled', true).html('<i class="bi bi-arrow-repeat spin me-1"></i> Exporting...');

    $.ajax({
        url: '{{ route('inventory.export') }}',
        method: 'GET',
        xhrFields: {
            responseType: 'blob'
        },
        success: function(blob, status, xhr) {
            const disposition = xhr.getResponseHeader('Content-Disposition');
            let filename = "inventory_report.xlsx";
            if (disposition && disposition.indexOf('attachment') !== -1) {
                const matches = /filename[^;=\n]*=((['"]).*?\2|[^;\n]*)/.exec(disposition);
                if (matches != null && matches[1]) {
                    filename = matches[1].replace(/['"]/g, '');
                }
            }

            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = filename;
            document.body.appendChild(a);
            a.click();
            a.remove();
            window.URL.revokeObjectURL(url);
        },
        error: function() {
            alert('Export failed. Please try again.');
        },
        complete: function() {
            $btn.prop('disabled', false).html('<i class="bi bi-download me-1"></i> Export');
        }
    });
});


</script>
@endsection