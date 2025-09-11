@extends('layouts.auth')

@section('page-title', 'Login')

@section('content')

<div class="form-wrapper">
    <div class="container">
        <div class="card">
            <div class="row g-0">
                <div class="col">
                    <div class="row">
                        <div class="col-md-10 offset-md-1">
                            <div class="d-block d-lg-none text-center text-lg-start">
                                <img width="120" src="{{ url('assets/images/logo.png') }}" alt="logo">
                            </div>
                            <div class="my-5 text-center text-lg-start">
                                <h1 class="display-8">Sign In</h1>
                                <p class="text-muted">Sign in to Dashboard</p>
                            </div>
                            <form class="mb-5" method="POST" action="{{ route('login') }}">
                                @csrf
                                <div class="mb-3">
                                    <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" placeholder="Enter email" value="{{ old('email') }}" required autocomplete="email" autofocus>
                                    @error('email')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                    @enderror
                                </div>
                                <div class="mb-3">
                                    <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" placeholder="Enter password" required autocomplete="current-password">
                                    @error('password')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                    @enderror
                                </div>
                                <div class="text-center text-lg-start">
                                    @if (Route::has('password.request'))
                                    <p class="small">Can't access your account? <a href="{{ route('password.request') }}">Reset your password now</a>.</p>
                                    @endif
                                    <button class="btn btn-primary">Sign In</button>
                                </div>
                            </form>
                            {{-- <div class="social-links justify-content-center">
                                    <a href="#">
                                        <i class="ti-google bg-google"></i> Sign in with Google
                                    </a>
                                    <a href="#">
                                        <i class="ti-facebook bg-facebook"></i> Sign in with Facebook
                                    </a>
                                </div>
                                <p class="text-center d-block d-lg-none mt-5 mt-lg-0">
                                    Don't have an account? <a href="#">Sign up</a>.
                                </p> --}}
                        </div>
                    </div>
                </div>
                <div class="col d-none d-lg-flex border-start align-items-center justify-content-between flex-column text-center">
                    <div class="logo">
                        <img width="225" src="{{ url('assets/images/logo.png') }}" alt="logo">
                    </div>
                    <div>

                        <p class="lead my-5" style="font-size: 1rem">Lorem ipsum dolor, sit amet consectetur adipisicing elit. Distinctio obcaecati eum enim quam eius aliquid, </p>
                        <p class="lead my-5" style="font-size: 1rem">velit rerum praesentium ipsam necessitatibus omnis consequuntur fuga quod, fugiat aliquam suscipit debitis nulla tenetur. </p>
                        {{-- <a href="#" class="btn btn-primary">Sign Up</a> --}}
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>

@endsection


{{-- @extends('layouts.auth')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">{{ __('Login') }}</div>

<div class="card-body">
    <form method="POST" action="{{ route('login') }}">
        @csrf

        <div class="row mb-3">
            <label for="email" class="col-md-4 col-form-label text-md-end">{{ __('Email Address') }}</label>

            <div class="col-md-6">
                <input id="email" type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" required autocomplete="email" autofocus>

                @error('email')
                <span class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </span>
                @enderror
            </div>
        </div>

        <div class="row mb-3">
            <label for="password" class="col-md-4 col-form-label text-md-end">{{ __('Password') }}</label>

            <div class="col-md-6">
                <input id="password" type="password" class="form-control @error('password') is-invalid @enderror" name="password" required autocomplete="current-password">

                @error('password')
                <span class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </span>
                @enderror
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-md-6 offset-md-4">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>

                    <label class="form-check-label" for="remember">
                        {{ __('Remember Me') }}
                    </label>
                </div>
            </div>
        </div>

        <div class="row mb-0">
            <div class="col-md-8 offset-md-4">
                <button type="submit" class="btn btn-primary">
                    {{ __('Login') }}
                </button>

                @if (Route::has('password.request'))
                <a class="btn btn-link" href="{{ route('password.request') }}">
                    {{ __('Forgot Your Password?') }}
                </a>
                @endif
            </div>
        </div>
    </form>
</div>
</div>
</div>
</div>
</div>
@endsection --}}