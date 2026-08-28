<li class="nav-item">
    <a class="nav-link {{ request()->routeIs('login') ? 'active' : '' }}" href="{{ route('login') }}">Login</a>
</li>
<li class="nav-item">
    <a class="nav-link {{ request()->routeIs('register') ? 'active' : '' }}" href="{{ route('register') }}">Register</a>
</li>
<li class="nav-item">
    <a class="nav-link {{ request()->routeIs('hosts.create') ? 'active' : '' }}" href="{{ route('hosts.create') }}">Create Host Account</a>
</li>
