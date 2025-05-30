<header>
    <h2 class="text-lg font-medium text-gray-900">
        {{ __('Two Factor Authentication') }}
    </h2>
    <p class="mt-1 text-sm text-gray-600">
        {{ __('Add additional security to your account using two factor authentication.') }}
    </p>
</header>

@if (! auth()->user()->two_factor_secret)
    {{-- Enable 2FA --}}
    <form method="post" action="{{ route('two-factor.enable') }}" class="mt-6">
        @csrf
        <x-primary-button>
            {{ __('Enable Two-Factor Authentication') }}
        </x-primary-button>
    </form>
@else
    {{-- Show 2FA QR Code and Recovery Codes --}}
    <div class="mt-6">
        <p class="text-sm text-gray-600">
            {{ __('Two factor authentication is now enabled. Scan the following QR code using your phone\'s authenticator application.') }}
        </p>

        <div class="mt-4">
            {!! auth()->user()->twoFactorQrCodeSvg() !!}
        </div>
    </div>

    <div class="mt-6">
        <p class="text-sm text-gray-600">
            {{ __('Store these recovery codes in a secure password manager. They can be used to recover access to your account if your two factor authentication device is lost.') }}
        </p>

        <div class="grid gap-1 max-w-xl mt-4 px-4 py-4 font-mono text-sm bg-gray-100 rounded-lg">
            @foreach (json_decode(decrypt(auth()->user()->two_factor_recovery_codes), true) as $code)
                <div>{{ $code }}</div>
            @endforeach
        </div>
    </div>

    {{-- Regenerate Recovery Codes --}}
    <form method="post" action="{{ route('two-factor.recovery-codes') }}" class="mt-6">
        @csrf
        <x-primary-button>
            {{ __('Regenerate Recovery Codes') }}
        </x-primary-button>
    </form>

    {{-- Disable 2FA --}}
    <form method="post" action="{{ route('two-factor.disable') }}" class="mt-6">
        @csrf
        @method('delete')
        <x-danger-button>
            {{ __('Disable Two-Factor Authentication') }}
        </x-danger-button>
    </form>
@endif 