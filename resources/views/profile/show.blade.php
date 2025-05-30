<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Profile') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-gray-200">
                <div class="p-6 sm:p-8">
                    <div class="max-w-3xl mx-auto">
                        <!-- Profile Header -->
                        <div class="flex flex-col sm:flex-row items-center gap-6 mb-8">
                            <div class="relative group">
                                <img src="{{ $user->avatar_url }}" alt="{{ $user->name }}" 
                                    class="h-32 w-32 rounded-full object-cover border-4 border-white shadow-lg">
                            </div>
                            <div class="text-center sm:text-left">
                                <h1 class="text-2xl font-bold text-gray-900">{{ $user->name }}</h1>
                                @if($user->location)
                                    <p class="text-gray-600 mt-1">
                                        <i class="fas fa-map-marker-alt mr-2"></i>{{ $user->location }}
                                    </p>
                                @endif
                                <p class="text-gray-500 mt-2">
                                    <i class="fas fa-envelope mr-2"></i>{{ $user->email }}
                                </p>
                            </div>
                        </div>

                        <!-- Bio Section -->
                        @if($user->bio)
                            <div class="mt-8">
                                <h2 class="text-lg font-semibold text-gray-900 mb-3">About</h2>
                                <div class="bg-gray-50 rounded-lg p-6 border border-gray-100">
                                    <p class="text-gray-700 leading-relaxed">{{ $user->bio }}</p>
                                </div>
                            </div>
                        @endif

                        <!-- Stats Section -->
                        <div class="mt-8 grid grid-cols-1 sm:grid-cols-3 gap-4">
                            <div class="bg-indigo-50 rounded-lg p-6 text-center border border-indigo-100">
                                <h3 class="text-2xl font-bold text-indigo-600">{{ $user->itineraries_count }}</h3>
                                <p class="text-indigo-700 mt-1">Itineraries</p>
                            </div>
                            <div class="bg-green-50 rounded-lg p-6 text-center border border-green-100">
                                <h3 class="text-2xl font-bold text-green-600">{{ $user->reviews_count }}</h3>
                                <p class="text-green-700 mt-1">Reviews</p>
                            </div>
                            <div class="bg-purple-50 rounded-lg p-6 text-center border border-purple-100">
                                <h3 class="text-2xl font-bold text-purple-600">{{ $user->favorites_count }}</h3>
                                <p class="text-purple-700 mt-1">Favorites</p>
                            </div>
                        </div>

                        <!-- Recent Activity -->
                        <div class="mt-8">
                            <h2 class="text-lg font-semibold text-gray-900 mb-4">Recent Activity</h2>
                            <div class="space-y-4">
                                @forelse($user->recent_activities as $activity)
                                    <div class="bg-white rounded-lg p-4 border border-gray-200 shadow-sm hover:shadow-md transition-shadow duration-200">
                                        <div class="flex items-start gap-4">
                                            <div class="flex-shrink-0">
                                                <i class="fas {{ $activity->icon }} text-2xl text-indigo-600"></i>
                                            </div>
                                            <div class="flex-1">
                                                <p class="text-gray-800">{{ $activity->description }}</p>
                                                <p class="text-sm text-gray-500 mt-1">{{ $activity->created_at->diffForHumans() }}</p>
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                    <div class="text-center py-8">
                                        <p class="text-gray-500">No recent activity</p>
                                    </div>
                                @endforelse
                            </div>
                        </div>

                        <!-- Edit Profile Button -->
                        <div class="mt-8 flex justify-center">
                            <a href="{{ route('profile.edit') }}" 
                               class="inline-flex items-center px-6 py-3 bg-indigo-600 border border-transparent rounded-md font-semibold text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors duration-200">
                                <i class="fas fa-edit mr-2"></i>
                                Edit Profile
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout> 