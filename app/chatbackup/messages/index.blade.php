<x-app-layout>
    <x-slot name="header">
        <h2 class="h4 mb-0">Messages</h2>
    </x-slot>

    <div class="container py-4">
        <div class="row justify-content-center">
            <div class="col-md-8">
                @forelse($conversations as $userId => $messages)
                    @php
                        $otherUser = $messages->first()->sender_id === auth()->id() 
                            ? $messages->first()->receiver 
                            : $messages->first()->sender;
                        $lastMessage = $messages->first();
                        $unreadCount = $messages->where('receiver_id', auth()->id())
                            ->where('read_at', null)
                            ->count();
                    @endphp
                    <div class="card shadow-sm mb-3">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div class="d-flex align-items-center">
                                    <img src="{{ $otherUser->getAvatarThumbUrlAttribute() }}" 
                                         alt="{{ $otherUser->name }}" 
                                         class="rounded-circle me-3" 
                                         style="width: 50px; height: 50px; object-fit: cover;">
                                    <div>
                                        <h5 class="mb-1">{{ $otherUser->name }}</h5>
                                        <p class="text-muted mb-0 small">
                                            {{ Str::limit($lastMessage->message, 50) }}
                                        </p>
                                    </div>
                                </div>
                                <div class="text-end">
                                    <small class="text-muted d-block">
                                        {{ $lastMessage->created_at->diffForHumans() }}
                                    </small>
                                    @if($unreadCount > 0)
                                        <span class="badge bg-primary rounded-pill">
                                            {{ $unreadCount }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                            <a href="{{ route('messages.conversation', $otherUser) }}" 
                               class="stretched-link"></a>
                        </div>
                    </div>
                @empty
                    <div class="card shadow-sm">
                        <div class="card-body text-center py-5">
                            <i class="bi bi-chat-dots display-1 text-muted mb-3"></i>
                            <h3 class="h5">No Messages Yet</h3>
                            <p class="text-muted">Start a conversation with other users!</p>
                        </div>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</x-app-layout> 