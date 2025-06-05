<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="h4 mb-0">Conversation with {{ $user->name }}</h2>
            <a href="{{ route('messages.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-2"></i>Back to Messages
            </a>
        </div>
    </x-slot>

    <div class="container-fluid px-0">
        <div class="row g-0">
            <div class="col-md-8 mx-auto">
                <div class="chat-container" style="position: fixed; bottom: 0; left: 0; right: 0; top: 160px; background: #f8f9fa;">
                    <div class="card h-100 border-0 rounded-0">
                        <div class="card-body d-flex flex-column p-0">
                            <!-- Messages -->
                            <div class="messages-container flex-grow-1 p-3" style="overflow-y: auto; height: calc(100vh - 260px);">
                                @forelse($messages->reverse() as $message)
                                    <div class="message mb-3 {{ $message->sender_id === auth()->id() ? 'text-end' : '' }}">
                                        <div class="message-content d-inline-block p-3 rounded {{ $message->sender_id === auth()->id() ? 'bg-primary text-white' : 'bg-light' }}" style="max-width: 75%;">
                                            <p class="mb-1">{{ $message->message }}</p>
                                            <small class="{{ $message->sender_id === auth()->id() ? 'text-white-50' : 'text-muted' }}">
                                                {{ $message->created_at->format('M d, Y H:i') }}
                                                @if($message->sender_id === auth()->id())
                                                    @if($message->read_at)
                                                        <i class="bi bi-check2-all text-success ms-1" title="Read at {{ $message->read_at->format('M d, Y H:i') }}"></i>
                                                    @else
                                                        <i class="bi bi-check2 text-muted ms-1" title="Sent"></i>
                                                    @endif
                                                @endif
                                            </small>
                                        </div>
                                    </div>
                                @empty
                                    <div class="text-center text-muted py-4">
                                        No messages yet. Start the conversation!
                                    </div>
                                @endforelse
                            </div>

                            <!-- Message Form -->
                            <div class="message-form p-3 bg-white" style="position: sticky; bottom: 0;">
                                <form action="{{ route('messages.store') }}" method="POST" class="position-relative">
                                    @csrf
                                    <input type="hidden" name="receiver_id" value="{{ $user->id }}">
                                    <div class="input-group">
                                        <textarea name="message" class="form-control" rows="2" placeholder="Type your message..." required></textarea>
                                        <button type="submit" class="btn btn-primary">
                                            <i class="bi bi-send me-2"></i>Send
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Scroll to bottom of messages container
            const container = document.querySelector('.messages-container');
            if (container) {
                container.scrollTop = container.scrollHeight;
            }

            // Auto-resize textarea
            const textarea = document.querySelector('textarea[name="message"]');
            if (textarea) {
                textarea.addEventListener('input', function() {
                    this.style.height = 'auto';
                    this.style.height = (this.scrollHeight) + 'px';
                });
            }

            // Real-time chat: Listen for new messages
            @if(Auth::check())
            window.Echo.private('messages.{{ Auth::id() }}')
                .listen('.message.sent', (e) => {
                    // If the message is from the current conversation partner, append it
                    if (parseInt(e.sender_id) === {{ $user->id }}) {
                        const msgHtml = `
                            <div class="message mb-3">
                                <div class="message-content d-inline-block p-3 rounded bg-light" style="max-width: 75%;">
                                    <p class="mb-1">${e.message}</p>
                                    <small class="text-muted">${e.created_at}</small>
                                </div>
                            </div>
                        `;
                        container.insertAdjacentHTML('beforeend', msgHtml);
                        container.scrollTop = container.scrollHeight;
                    } else {
                        // Show a toast notification for messages from other users
                        showToast(`${e.sender_name}: ${e.message}`);
                    }
                });

            // Simple toast notification
            function showToast(message) {
                let toast = document.getElementById('live-toast');
                if (!toast) {
                    toast = document.createElement('div');
                    toast.id = 'live-toast';
                    toast.style.position = 'fixed';
                    toast.style.bottom = '30px';
                    toast.style.right = '30px';
                    toast.style.background = '#333';
                    toast.style.color = '#fff';
                    toast.style.padding = '16px 24px';
                    toast.style.borderRadius = '8px';
                    toast.style.zIndex = 9999;
                    toast.style.boxShadow = '0 2px 8px rgba(0,0,0,0.2)';
                    document.body.appendChild(toast);
                }
                toast.textContent = message;
                toast.style.display = 'block';
                setTimeout(() => { toast.style.display = 'none'; }, 4000);
            }
            @endif
        });
    </script>
    @endpush
</x-app-layout> 