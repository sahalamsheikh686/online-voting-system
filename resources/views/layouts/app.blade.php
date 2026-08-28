<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ config('app.name') }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/css/online-voting-system.css?v={{ filemtime(public_path('css/online-voting-system.css')) }}">
    @stack('styles')
</head>
<body>
    @guest
        <div class="guest-shell">
            <nav class="guest-topnav navbar navbar-expand-lg">
                <div class="container-fluid guest-topnav-wrap">
                    @include('partials.navbar.brand')
                    <button class="navbar-toggler guest-topnav-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#guestTopNav" aria-controls="guestTopNav" aria-expanded="false" aria-label="Toggle navigation">
                        <span class="navbar-toggler-icon"></span>
                    </button>
                    <div class="collapse navbar-collapse justify-content-end" id="guestTopNav">
                        <ul class="navbar-nav guest-topnav-list">
                            @include('partials.navbar.guest-auth')
                        </ul>
                    </div>
                </div>
            </nav>

            <main class="guest-main pt-4 pb-0">
                <div class="container-fluid app-content-wrap">
                    @include('partials.alerts')
                    @yield('content')
                </div>
            </main>
            <footer class="app-footer">
                <div class="app-footer-inner">Your Vote, Your Right — सुरक्षित अनलाइन मतदान प्रणाली।</div>
            </footer>
        </div>
    @else
        <div class="app-shell">
            <aside class="app-sidebar">
                <div class="app-sidebar-header">
                    @include('partials.navbar.brand')
                    <button class="navbar-toggler sidebar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#appSidebarNav" aria-controls="appSidebarNav" aria-expanded="false" aria-label="Toggle navigation">
                        <span class="navbar-toggler-icon"></span>
                    </button>
                </div>

                <div class="collapse d-lg-block app-sidebar-nav" id="appSidebarNav">
                    <ul class="navbar-nav flex-column app-nav-list">
                        @if(auth()->user()->isAdmin())
                            @include('partials.navbar.admin-dashboard')
                            @include('partials.navbar.admin-add-election-card')
                            @include('partials.navbar.admin-candidates')
                            @include('partials.navbar.admin-users')
                            @include('partials.navbar.admin-admins')
                            @include('partials.navbar.admin-host-requests')
                            @include('partials.navbar.admin-reports')
                        @elseif(auth()->user()->isHost())
                            @include('partials.navbar.admin-dashboard')
                            @include('partials.navbar.admin-add-election-card')
                            @include('partials.navbar.admin-candidates')
                            @include('partials.navbar.admin-users')
                            @include('partials.navbar.admin-reports')
                        @else
                            @include('partials.navbar.user-vote')
                        @endif
                    </ul>
                </div>
            </aside>

            <main class="app-main pt-4">
                <div class="container-fluid app-content-wrap">
                    <div class="app-topbar mb-4">
                        <div class="app-topbar-content">
                            <div class="app-topbar-eyebrow">
                                @yield('topbar_eyebrow', auth()->user()->isAdmin() ? 'Control Center' : (auth()->user()->isHost() ? 'Host Center' : 'Voting Portal'))
                            </div>
                            <div class="app-topbar-title">
                                @yield('topbar_title', auth()->user()->isAdmin() ? 'Welcome back, '.auth()->user()->name : (auth()->user()->isHost() ? 'Welcome host, '.auth()->user()->name : 'Welcome, '.auth()->user()->name))
                            </div>
                            <div class="app-topbar-text">
                                @yield('topbar_text', auth()->user()->isAdmin()
                                    ? 'Monitor elections, manage candidates, and keep the election system running smoothly from one place.'
                                    : (auth()->user()->isHost()
                                        ? 'Manage your election cards, candidates, voters, and reports from one focused host workspace.'
                                        : 'Review the latest election updates and cast your vote securely from this portal.'))
                            </div>
                        </div>
                        @include('partials.navbar.account')
                    </div>
                    @include('partials.alerts')
                    @yield('content')
                </div>
                <footer class="app-footer">
                    <div class="app-footer-inner">Your Vote, Your Right — सुरक्षित अनलाइन मतदान प्रणाली।</div>
                </footer>
            </main>
        </div>
    @endguest

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
    @stack('scripts')
</body>
</html>
