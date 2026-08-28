<a class="navbar-brand fw-semibold" href="{{ auth()->check() ? (auth()->user()->isAdmin() || auth()->user()->isHost() ? route('dashboard') : route('vote.index')) : route('login') }}">
    Online Voting System
</a>
