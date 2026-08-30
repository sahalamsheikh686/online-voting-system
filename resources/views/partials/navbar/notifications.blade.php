@php
    $notificationRoute = null;
    $notificationLabel = '';
    $notificationCount = 0;

    if (auth()->user()->isAdmin()) {
        $notificationRoute = route('host-requests.index');
        $notificationLabel = 'Pending host requests';
        $notificationCount = \App\Models\User::query()
            ->where('role', 'host')
            ->where('status', 'pending')
            ->count();
    } elseif (auth()->user()->isHost()) {
        $notificationRoute = route('users.index');
        $notificationLabel = 'Pending voter registrations for your elections';
        $notificationCount = \App\Models\User::query()
            ->where('role', 'user')
            ->where('status', 'pending')
            ->whereHas('election', fn ($query) => $query->where('host_id', auth()->id()))
            ->count();
    }
@endphp

@if($notificationRoute)
    <a href="{{ $notificationRoute }}" class="app-notification-bell" title="{{ $notificationLabel }}" aria-label="{{ $notificationLabel }}">
        <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
            <path d="M18 8a6 6 0 1 0-12 0c0 7-3 9-3 9h18s-3-2-3-9"></path>
            <path d="M13.73 21a2 2 0 0 1-3.46 0"></path>
        </svg>
        @if($notificationCount > 0)
            <span class="app-notification-badge">{{ $notificationCount > 99 ? '99+' : $notificationCount }}</span>
        @endif
    </a>
@endif
