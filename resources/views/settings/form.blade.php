@extends('layouts.app')


@section('page-title', 'Settings')
@section('head')
<link rel="stylesheet" href="{{ url("libs/select2/css/select2.min.css") }}" type="text/css">
@endsection
@section('content')
    
    <div class="mb-4">
        <nav style="--bs-breadcrumb-divider: '>';" aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="{{ route('dashboard') }}">
                        <i class="bi bi-globe2 small me-2"></i> Dashboard
                    </a>
                </li>
                <li class="breadcrumb-item active" aria-current="page">Settings</li>
            </ol>
        </nav>
    </div>

    <div class="row flex-column-reverse flex-md-row">
        <div class="col-md-12">
            <div class="card mb-4">
                    <div class="card-body">
                        <h6 class="card-title mb-4">{{ $platform }} Settings</h6>
                        <form method="POST" action="{{route('setting.store')}}">
                            @csrf
                            <div class="row">
                                @foreach ($settingData as  $setting)
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">{{ $setting->title }}</label>
                                            @if($setting->field_type=='select')
                                              <select name="{{ $setting->code }}" class="form-select">
                                                @if(!empty($setting->data_source)&&isset($dataSets[$setting->code]))

                                                    @foreach($dataSets[$setting->code] as $key=> $dataset)
                                                            <option value="{{ $key }}" {{ ($dataset==$setting->value) ? 'selected' : '' }}>{{ $dataset }}</option>
                                                        @endforeach
                                                
                                                @elseif(!empty($setting->dataset))
                                                  
                                                        @foreach(json_decode($setting->dataset) as $dataset)
                                                        <option value="{{ $dataset->id }}" {{ ($dataset->id==$setting->value) ? 'selected' : '' }}>{{ $dataset->name }}</option>
                                                        @endforeach
                                                   
                                                @endif
                                                 </select>
                                            @elseif($setting->field_type=='multiple')
                                                @php $dataSelected=explode(',',$setting->value);@endphp
                                            <select name="{{ $setting->code }}[]" class="select2" multiple>
                                                @foreach(json_decode($setting->dataset) as $dataset)
                                                <option value="{{ $dataset->id }}" {{in_array($dataset->id, $dataSelected) ? 'selected' : '' }}>{{ $dataset->name }}</option>
                                                @endforeach
                                            </select>
                                            @elseif($setting->field_type=='textarea')
                                                <textarea class="form-control" rows="4" name="{{ $setting->code }}">{{ $setting->value }}</textarea>
                                            @else 
                                                <input type="{{ $setting->field_type }}" name="{{ $setting->code }}" class="form-control"
                                                value="{{ $setting->value }}" placeholder="{{ $setting->placeholder }}">
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                                <div class="col-md-12">
                                    <div class="mb-3">
                                        <input type="hidden" value="{{ $platform }}" name="platform">
                                        <button type="submit" class="btn btn-primary mb-3 pull-right">Save</button>
                                        @if($platform == 'Mail')
                                            <button type="button" id="sendTestMailBtn" class="btn btn-primary mb-3 pull-right" style="margin-right: 10px;">
                                                Test Mail
                                            </button>
                                        @endif

                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
           
    </div>
  
@endsection
@section('script')

<script src="{{ url('libs/select2/js/select2.min.js') }}"></script>
<script>
    $('.select2').select2({ placeholder: 'Select' });

    @if(Session::has('success_message'))
        Swal.fire({
            icon: 'success',
            title: '{{ Session::get('success_message') }}'
        });
    @endif
</script>

@endsection