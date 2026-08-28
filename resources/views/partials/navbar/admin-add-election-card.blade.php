<li class="nav-item">
    <a
        class="nav-link {{ request()->routeIs('elections.create') ? 'active' : '' }}"
        href="{{ route('elections.create') }}"
    >
        Add Election Card
    </a>
</li>
