<x-admin-layout>
    <x-slot:header>
        Payout Requests
    </x-slot>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>User</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th>Requested</th>
                            <th>Processed</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($payouts as $payout)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        @if($payout->user->avatar)
                                            <img src="{{ Storage::url($payout->user->avatar) }}" alt="{{ $payout->user->name }}" class="rounded-circle me-2" style="width: 32px; height: 32px;">
                                        @else
                                            <div class="bg-primary bg-opacity-10 rounded-circle p-2 me-2">
                                                <i class="bi bi-person text-primary"></i>
                                            </div>
                                        @endif
                                        {{ $payout->user->name }}
                                    </div>
                                </td>
                                <td>${{ number_format($payout->amount, 2) }}</td>
                                <td>
                                    <span class="badge bg-{{ $payout->status === 'approved' ? 'success' : ($payout->status === 'rejected' ? 'danger' : 'warning') }}">
                                        {{ ucfirst($payout->status) }}
                                    </span>
                                </td>
                                <td>{{ $payout->created_at->format('M d, Y') }}</td>
                                <td>{{ $payout->processed_at ? $payout->processed_at->format('M d, Y') : '-' }}</td>
                                <td>
                                    @if($payout->status === 'pending')
                                        <form action="{{ route('admin.payouts.approve', $payout) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-success">
                                                <i class="bi bi-check-lg"></i> Approve
                                            </button>
                                        </form>
                                        <form action="{{ route('admin.payouts.reject', $payout) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-danger">
                                                <i class="bi bi-x-lg"></i> Reject
                                            </button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center">No payout requests found</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $payouts->links() }}
            </div>
        </div>
    </div>
</x-admin-layout> 