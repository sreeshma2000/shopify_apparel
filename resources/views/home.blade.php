@extends('layouts.app')

@section('page-title', 'Overview')

@section('head')

@endsection


@section('content')
<div class="row row-cols-1 row-cols-md-3 g-4">
    <div class="col-lg-12 col-md-12">
        <div class="card widget">
            <div class="card-header">
                <h5 class="card-title"></h5>
            </div>
            <div class="row g-4">
                <div class="col-md-6">

                    {{-- <div class="card border-0">
                        <div class="card-body text-center">
                            <div class="display-5">
                            </div>
                        </div>
                    </div> --}}

                </div>


            </div>
        </div>
    </div>
</div>

@endsection

@section('script')
<script src="{{ url("libs/charts/apex/apexcharts.min.js") }}"></script>

<script src="{{ url("libs/slick/slick.min.js") }}"></script>

<script src="{{ url("dist/js/examples/dashboard.js") }}"></script>
@endsection