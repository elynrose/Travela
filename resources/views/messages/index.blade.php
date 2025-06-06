<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Messages') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Conversations List -->
                <div class="lg:col-span-1">
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-4 border-b border-gray-200">
                            <h3 class="text-lg font-medium text-gray-900">Conversations</h3>
                        </div>
                        <div class="divide-y divide-gray-200">
                            @forelse($conversations as $conversation)
                                <a href="{{ route('messages.show', $conversation->id) }}" 
                                   class="block p-4 hover:bg-gray-50 {{ request()->route('conversation') == $conversation->id ? 'bg-gray-50' : '' }}">
                                    <div class="flex items-center space-x-4">
                                        <div class="flex-shrink-0">
                                            <img class="h-12 w-12 rounded-full" 
                                                 src="{{ $conversation->other_user->profile_photo_url }}" 
                                                 alt="{{ $conversation->other_user->name }}">
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <div class="flex items-center justify-between">
                                                <p class="text-sm font-medium text-gray-900 truncate">
                                                    {{ $conversation->other_user->name }}
                                                </p>
                                                @if($conversation->last_message && $conversation->last_message->created_at)
                                                    <p class="text-xs text-gray-500">
                                                        {{ $conversation->last_message->created_at->diffForHumans() }}
                                                    </p>
                                                @endif
                                            </div>
                                            @if($conversation->last_message)
                                                <p class="text-sm text-gray-500 truncate">
                                                    {{ $conversation->last_message->content }}
                                                </p>
                                            @endif
                                            @if($conversation->unread_count > 0)
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800">
                                                    {{ $conversation->unread_count }}
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                </a>
                            @empty
                                <div class="p-4 text-center text-gray-500">
                                    No conversations found.
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>

                <!-- Messages -->
                <div class="lg:col-span-2">
                    @if(isset($conversation))
                        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg h-[600px] flex flex-col">
                            <!-- Conversation Header -->
                            <div class="p-4 border-b border-gray-200">
                                <div class="flex items-center space-x-4">
                                    <img class="h-10 w-10 rounded-full" 
                                         src="{{ $conversation->other_user->profile_photo_url }}" 
                                         alt="{{ $conversation->other_user->name }}">
                                    <div>
                                        <h3 class="text-lg font-medium text-gray-900">
                                            {{ $conversation->other_user->name }}
                                        </h3>
                                        <p class="text-sm text-gray-500">
                                            {{ $conversation->other_user->email }}
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <!-- Messages List -->
                            <div class="flex-1 overflow-y-auto p-4 space-y-4" id="messages-container">
                                @foreach($messages as $message)
                                    <div class="flex {{ $message->sender_id === auth()->id() ? 'justify-end' : 'justify-start' }}">
                                        <div class="max-w-lg {{ $message->sender_id === auth()->id() ? 'bg-indigo-100' : 'bg-gray-100' }} rounded-lg px-4 py-2">
                                            <p class="text-sm text-gray-900">{{ $message->content }}</p>
                                            <p class="text-xs text-gray-500 mt-1">
                                                {{ $message->created_at->format('M d, Y H:i') }}
                                            </p>
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            <!-- Message Input -->
                            <div class="p-4 border-t border-gray-200">
                                <form action="{{ route('messages.store', $conversation->id) }}" method="POST" class="flex space-x-4">
                                    @csrf
                                    <div class="flex-1">
                                        <textarea name="content" rows="1" 
                                                  class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                                  placeholder="Type your message..."></textarea>
                                    </div>
                                    <button type="submit" 
                                            class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                        Send
                                    </button>
                                </form>
                            </div>
                        </div>
                    @else
                        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg h-[600px] flex items-center justify-center">
                            <div class="text-center">
                                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                                </svg>
                                <h3 class="mt-2 text-sm font-medium text-gray-900">No conversation selected</h3>
                                <p class="mt-1 text-sm text-gray-500">Select a conversation from the list to start messaging.</p>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    @if(isset($conversation))
        @push('scripts')
        <script>
            // Scroll to bottom of messages container
            const messagesContainer = document.getElementById('messages-container');
            messagesContainer.scrollTop = messagesContainer.scrollHeight;

            // Auto-resize textarea
            const textarea = document.querySelector('textarea[name="content"]');
            textarea.addEventListener('input', function() {
                this.style.height = 'auto';
                this.style.height = (this.scrollHeight) + 'px';
            });
        </script>
        @endpush
    @endif
</x-app-layout> 