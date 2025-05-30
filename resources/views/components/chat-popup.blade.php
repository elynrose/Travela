<div class="chat-popup" id="chatPopup" style="display: none;">
    <div class="chat-header d-flex justify-content-between align-items-center p-3 bg-primary text-white">
        <div class="d-flex align-items-center">
            <img src="{{ Storage::url($itinerary->user->avatar) }}" alt="{{ $itinerary->user->name }}" 
                 class="rounded-circle me-2" style="width: 32px; height: 32px;">
            <div>
                <h6 class="mb-0">{{ $itinerary->user->name }}</h6>
                <small>Itinerary Creator</small>
            </div>
        </div>
        <button type="button" class="btn-close btn-close-white" onclick="toggleChat()"></button>
    </div>

    <div class="chat-body p-3" style="height: 400px; overflow-y: auto;">
        <div id="messagesList">
            @foreach($messages as $message)
                <div class="message {{ $message->user_id === auth()->id() ? 'message-sent' : 'message-received' }} mb-3">
                    <div class="message-content p-2 rounded {{ $message->user_id === auth()->id() ? 'bg-primary text-white' : 'bg-light' }}">
                        {{ $message->content }}
                    </div>
                    <small class="text-muted">{{ $message->created_at->format('M d, H:i') }}</small>
                </div>
            @endforeach
        </div>
    </div>

    <div class="chat-footer p-3 border-top">
        <form id="messageForm" class="d-flex gap-2">
            <input type="text" class="form-control" id="messageInput" placeholder="Type your message...">
            <button type="submit" class="btn btn-primary">
                <i class="bi bi-send"></i>
            </button>
        </form>
    </div>
</div>

<button class="btn btn-primary position-fixed bottom-0 end-0 m-4 rounded-circle shadow" 
        style="width: 60px; height: 60px;" onclick="toggleChat()">
    <i class="bi bi-chat-dots fs-4"></i>
</button>

@push('styles')
<style>
    .chat-popup {
        position: fixed;
        bottom: 80px;
        right: 20px;
        width: 350px;
        background: white;
        border-radius: 10px;
        box-shadow: 0 0 20px rgba(0,0,0,0.1);
        z-index: 1000;
    }

    .message {
        max-width: 80%;
    }

    .message-sent {
        margin-left: auto;
    }

    .message-received {
        margin-right: auto;
    }

    .message-content {
        word-break: break-word;
    }

    #messagesList {
        display: flex;
        flex-direction: column;
    }
</style>
@endpush

@push('scripts')
<script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>
<script>
    // Initialize Pusher
    const pusher = new Pusher('{{ config('broadcasting.connections.pusher.key') }}', {
        cluster: '{{ config('broadcasting.connections.pusher.options.cluster') }}'
    });

    // Subscribe to the channel
    const channel = pusher.subscribe('chat.{{ $itinerary->id }}');
    
    // Listen for new messages
    channel.bind('new-message', function(data) {
        appendMessage(data.message);
    });

    // Toggle chat popup
    function toggleChat() {
        const chatPopup = document.getElementById('chatPopup');
        chatPopup.style.display = chatPopup.style.display === 'none' ? 'block' : 'none';
    }

    // Handle form submission
    document.getElementById('messageForm').addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const input = document.getElementById('messageInput');
        const message = input.value.trim();
        
        if (!message) return;

        try {
            const response = await fetch('{{ route('messages.store') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    itinerary_id: {{ $itinerary->id }},
                    content: message
                })
            });

            if (response.ok) {
                input.value = '';
            }
        } catch (error) {
            console.error('Error sending message:', error);
        }
    });

    // Append new message to the chat
    function appendMessage(message) {
        const messagesList = document.getElementById('messagesList');
        const isSent = message.user_id === {{ auth()->id() }};
        
        const messageHtml = `
            <div class="message ${isSent ? 'message-sent' : 'message-received'} mb-3">
                <div class="message-content p-2 rounded ${isSent ? 'bg-primary text-white' : 'bg-light'}">
                    ${message.content}
                </div>
                <small class="text-muted">${new Date(message.created_at).toLocaleString()}</small>
            </div>
        `;
        
        messagesList.insertAdjacentHTML('beforeend', messageHtml);
        messagesList.scrollTop = messagesList.scrollHeight;
    }
</script>
@endpush 