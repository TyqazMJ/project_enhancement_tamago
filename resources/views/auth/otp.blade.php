@extends('layouts.app')

@section('content')
<div class="container">
    <h4>Two-Factor Authentication</h4>
    <p>Please enter the 6-digit code sent to your email.</p>

    <form method="POST" action="{{ url('/2fa') }}">
        @csrf
        <input type="text" name="otp_code" placeholder="Enter OTP" required>

        @error('otp_code')
            <div style="color:red;">{{ $message }}</div>
        @enderror

        <button type="submit">Verify</button>
    </form>
</div>
@endsection
