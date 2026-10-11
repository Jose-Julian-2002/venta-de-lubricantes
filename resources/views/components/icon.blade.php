@props(['name', 'class' => 'size-5'])

<svg
    {{ $attributes->merge(['class' => $class]) }}
    viewBox="0 0 24 24"
    fill="none"
    stroke="currentColor"
    stroke-width="1.8"
    stroke-linecap="round"
    stroke-linejoin="round"
    aria-hidden="true"
>
    @switch($name)
        @case('grid')
            <rect x="3.5" y="3.5" width="7" height="7" rx="1.5" />
            <rect x="13.5" y="3.5" width="7" height="7" rx="1.5" />
            <rect x="3.5" y="13.5" width="7" height="7" rx="1.5" />
            <rect x="13.5" y="13.5" width="7" height="7" rx="1.5" />
            @break
        @case('shield')
            <path d="M12 3.5 19 6v5.5c0 4.2-2.9 7.5-7 9-4.1-1.5-7-4.8-7-9V6l7-2.5Z" />
            <path d="m9 12 2 2 4-4" />
            @break
        @case('users')
            <path d="M16 20v-1.5a4 4 0 0 0-4-4H7a4 4 0 0 0-4 4V20" />
            <circle cx="9.5" cy="7.5" r="3.5" />
            <path d="M17 4.5a3.5 3.5 0 0 1 0 6.8M21 20v-1.5a4 4 0 0 0-3-3.9" />
            @break
        @case('sun')
            <circle cx="12" cy="12" r="4" />
            <path d="M12 2.5v2m0 15v2m9.5-9.5h-2m-15 0h-2m16.22-6.72-1.42 1.42M6.7 17.3l-1.42 1.42m13.44 0-1.42-1.42M6.7 6.7 5.28 5.28" />
            @break
        @case('moon')
            <path d="M20.2 15.1A8.5 8.5 0 0 1 8.9 3.8 8.5 8.5 0 1 0 20.2 15.1Z" />
            @break
        @case('logout')
            <path d="M10 4H5.5A1.5 1.5 0 0 0 4 5.5v13A1.5 1.5 0 0 0 5.5 20H10" />
            <path d="M14 16l4-4-4-4m4 4H9" />
            @break
        @case('login')
            <path d="M14 4h4.5A1.5 1.5 0 0 1 20 5.5v13a1.5 1.5 0 0 1-1.5 1.5H14" />
            <path d="m10 8-4 4 4 4m-4-4h9" />
            @break
        @case('menu')
            <path d="M4 6h16M4 12h16M4 18h16" />
            @break
        @case('plus')
            <path d="M12 5v14m-7-7h14" />
            @break
        @case('arrow-right')
            <path d="M4.5 12h15m-6-6 6 6-6 6" />
            @break
        @case('edit')
            <path d="m15 5 4 4M4 20l4.2-.8L19 8.4a2.1 2.1 0 0 0-3-3L5.2 16.2 4 20Z" />
            @break
        @case('trash')
            <path d="M4.5 7h15m-13.5 0 .8 13h9.4L17 7M9 7V4h6v3m-5 4v5m4-5v5" />
            @break
        @case('back')
            <path d="m14.5 18-6-6 6-6m-6 6h12" />
            @break
    @endswitch
</svg>
