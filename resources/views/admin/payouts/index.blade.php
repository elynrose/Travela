<x-admin-layout>
    <x-slot name="header">
        Payout Requests
    </x-slot>
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">All Payout Requests</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>User</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th>Requested</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($payouts as $payout)
                            <tr>
                                <td>{{ $payout->id }}</td>
                                <td>{{ $payout->user->name }}</td>
                                <td>${{ number_format($payout->amount, 2) }}</td>
                                <td>
                                    <span class="badge bg-{{ $payout->status === 'pending' ? 'warning' : ($payout->status === 'approved' ? 'success' : 'danger') }}">
                                        {{ ucfirst($payout->status) }}
                                    </span>
                                </td>
                                <td>{{ $payout->created_at->format('M d, Y') }}</td>
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
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="mt-4">
                {{ $payouts->links() }}
            </div>
        </div>
    </div>
</x-admin-layout> 