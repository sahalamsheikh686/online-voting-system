<div class="app-account-box">
    <div class="app-account-meta">
        <div class="app-account-label">Signed in as</div>
        <div class="app-account-name">{{ auth()->user()->name }}</div>
    </div>
    <form action="{{ route('logout') }}" method="POST">
        @csrf
        <button class="btn btn-sm app-logout-btn rounded-pill px-3">Logout</button>
    </form>
</div>
