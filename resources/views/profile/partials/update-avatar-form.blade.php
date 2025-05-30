<section>
    <header>
        <h2 class="text-lg font-medium text-gray-900">
            {{ __('Profile Picture') }}
        </h2>

        <p class="mt-1 text-sm text-gray-600">
            {{ __('Update your profile picture.') }}
        </p>
    </header>

    <div class="mt-6 space-y-6">
        <div class="flex items-center gap-x-6">
            <div class="relative">
                <img src="{{ $user->avatar_url }}" alt="{{ $user->name }}" class="h-24 w-24 rounded-full object-cover">
                <div class="absolute inset-0 flex items-center justify-center rounded-full bg-black bg-opacity-50 opacity-0 hover:opacity-100 transition-opacity">
                    <span class="text-white text-sm">Change</span>
                </div>
            </div>

            <form method="post" action="{{ route('profile.avatar') }}" enctype="multipart/form-data" class="flex-1">
                @csrf
                @method('patch')

                <div class="flex items-center gap-x-4">
                    <input type="file" name="avatar" id="avatar" accept="image/*" class="hidden" onchange="this.form.submit()">
                    <label for="avatar" class="cursor-pointer rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50">
                        {{ __('Choose new photo') }}
                    </label>
                    @if ($user->avatar)
                        <button type="submit" form="remove-avatar" class="text-sm font-semibold text-red-600 hover:text-red-500">
                            {{ __('Remove') }}
                        </button>
                    @endif
                </div>

                <x-input-error :messages="$errors->get('avatar')" class="mt-2" />
            </form>

            <form id="remove-avatar" method="post" action="{{ route('profile.avatar') }}" class="hidden">
                @csrf
                @method('delete')
            </form>
        </div>

        @if (session('status') === 'avatar-updated')
            <p
                x-data="{ show: true }"
                x-show="show"
                x-transition
                x-init="setTimeout(() => show = false, 2000)"
                class="text-sm text-gray-600"
            >{{ __('Profile picture updated.') }}</p>
        @endif
    </div>
</section> 