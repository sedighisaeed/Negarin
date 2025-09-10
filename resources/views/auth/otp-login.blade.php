@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-lg-5">
            <div class="card shadow-none border">
                <div class="card-header bg-transparent p-3">
                    <h4 class="font-weight-bold mb-0 text-center">
                        {{ __('auth.otpLoginTitle') }}
                    </h4>
                </div>

                @if ($errors->any())
                    @foreach ($errors->all() as $error)
                    <div class="alert alert-danger m-3">
                        <span class="font-weight-bold small"><i class="far fa-exclamation-triangle mr-2"></i> {{ $error }}</span>
                    </div>
                    @endforeach
                @endif

                <div class="card-body">
                    <div id="otp-form">
                        <div class="form-group row mb-0">
                            <div class="col-md-12">
                                <label for="phone" class="small font-weight-bold text-muted mb-0">{{ __('auth.phoneNumber') }}</label>
                                <input id="phone" type="tel" class="form-control" name="phone" placeholder="{{ __('auth.enterPhoneNumber') }}" required>
                            </div>
                        </div>

                        <button type="button" id="send-otp" class="btn btn-primary btn-block btn-lg font-weight-bold rounded-pill mt-3">
                            {{ __('auth.sendOtp') }}
                        </button>
                    </div>

                    <div id="verify-form" style="display: none;">
                        <div class="form-group row mb-0">
                            <div class="col-md-12">
                                <label for="otp-code" class="small font-weight-bold text-muted mb-0">{{ __('auth.enterOtp') }}</label>
                                <input id="otp-code" type="text" class="form-control" name="otp_code" placeholder="{{ __('auth.enterOtpCode') }}" maxlength="6" required>
                            </div>
                        </div>

                        <button type="button" id="verify-otp" class="btn btn-success btn-block btn-lg font-weight-bold rounded-pill mt-3">
                            {{ __('auth.verifyLogin') }}
                        </button>

                        <button type="button" id="back-to-phone" class="btn btn-secondary btn-block btn-sm rounded-pill mt-2">
                            {{ __('auth.changePhoneNumber') }}
                        </button>
                    </div>

                    <hr>

                    <p class="text-center font-weight-bold mb-0">
                        <a href="{{ route('login') }}">{{ __('auth.loginWithEmailPassword') }}</a>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/intl-tel-input@18.2.1/build/css/intlTelInput.css">
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/intl-tel-input@18.2.1/build/js/intlTelInput.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/intl-tel-input@18.2.1/build/js/utils.js"></script>
<script>
    // Localization for JavaScript
    window.otpTranslations = {
        invalidPhoneNumber: '{{ __("auth.invalidPhoneNumber") }}',
        sending: '{{ __("auth.sending") }}',
        sendOtp: '{{ __("auth.sendOtp") }}',
        failedToSendOtp: '{{ __("auth.failedToSendOtp") }}',
        networkIssue: '{{ __("auth.networkIssue") }}',
        invalidOtpLength: '{{ __("auth.invalidOtpLength") }}',
        verifying: '{{ __("auth.verifying") }}',
        invalidOtp: '{{ __("auth.invalidOtp") }}',
        verifyLogin: '{{ __("auth.verifyLogin") }}'
    };
</script>
<script>
document.addEventListener("DOMContentLoaded", function() {
    const input = document.querySelector("#phone");
    const iti = window.intlTelInput(input, {
        utilsScript: "https://cdn.jsdelivr.net/npm/intl-tel-input@18.2.1/build/js/utils.js",
        preferredCountries: ['us', 'gb', 'ir'],
        separateDialCode: true,
    });

    const sendOtpBtn = document.getElementById('send-otp');
    const verifyOtpBtn = document.getElementById('verify-otp');
    const backBtn = document.getElementById('back-to-phone');
    const otpForm = document.getElementById('otp-form');
    const verifyForm = document.getElementById('verify-form');
    const phoneInput = document.getElementById('phone');
    const otpInput = document.getElementById('otp-code');

    sendOtpBtn.addEventListener('click', function() {
        const phoneNumber = iti.getNumber();
        if (!phoneNumber) {
            alert(window.otpTranslations.invalidPhoneNumber);
            return;
        }

        sendOtpBtn.disabled = true;
        sendOtpBtn.textContent = window.otpTranslations.sending;

        fetch('{{ route("login.otp.send") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ phone: phoneNumber })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                otpForm.style.display = 'none';
                verifyForm.style.display = 'block';
                otpInput.focus();
            } else {
                alert(data.error || window.otpTranslations.failedToSendOtp);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert(window.otpTranslations.networkIssue);
        })
        .finally(() => {
            sendOtpBtn.disabled = false;
            sendOtpBtn.textContent = window.otpTranslations.sendOtp;
        });
    });

    verifyOtpBtn.addEventListener('click', function() {
        const otp = otpInput.value;
        if (!otp || otp.length !== 6) {
            alert(window.otpTranslations.invalidOtpLength);
            return;
        }

        verifyOtpBtn.disabled = true;
        verifyOtpBtn.textContent = window.otpTranslations.verifying;

        fetch('{{ route("login.otp.verify") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ otp_code: otp })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                window.location.href = data.redirect || '/';
            } else {
                alert(data.error || window.otpTranslations.invalidOtp);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert(window.otpTranslations.networkIssue);
        })
        .finally(() => {
            verifyOtpBtn.disabled = false;
            verifyOtpBtn.textContent = window.otpTranslations.verifyLogin;
        });
    });

    backBtn.addEventListener('click', function() {
        verifyForm.style.display = 'none';
        otpForm.style.display = 'block';
        otpInput.value = '';
        phoneInput.focus();
    });
});
</script>
@endpush