@props(['heading', 'description' => null, 'aside' => true])

<section @class([
    'fi-timeline-section',
    'bg-white shadow-sm rounded-xl ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10' => !$aside,
    'grid items-start grid-cols-1 gap-x-6 gap-y-4 md:grid-cols-3' => $aside,
])>
    <header @class([
        'fi-timeline-section-header',
        'flex flex-col gap-3 px-6 py-4 overflow-hidden sm:flex-row sm:items-center' => !$aside,
        'flex flex-col gap-3 overflow-hidden sm:flex-row sm:items-center' => $aside,
    ])>
        <div class="grid flex-1 gap-y-1">
            <h3
                class="text-base font-semibold leading-6 fi-timeline-section-header-heading text-gray-950 dark:text-white">
                {{ $heading }}
            </h3>
            <p class="text-sm text-gray-500 fi-timeline-section-header-description dark:text-gray-400">
                {{ $description }}
            </p>
        </div>
    </header>

    @if ($aside)
        <div
            class="p-6 bg-white shadow-sm fi-timeline-section-content md:col-span-2 rounded-xl ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
            {{ $slot }}
        </div>
    @else
        <div class="p-6 border-t border-gray-200 fi-timeline-section-content dark:border-white/10">
            {{ $slot }}
        </div>
    @endif
</section>
