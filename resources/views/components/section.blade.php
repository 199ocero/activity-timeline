@props(['heading', 'headingVisible' => true, 'description' => null, 'aside' => true, 'extraAttributes' => []])

<section
    {{ $attributes->merge($extraAttributes, escape: false)->class([
            'fi-timeline-section',
            'grid items-start grid-cols-1 gap-x-6 gap-y-4 md:grid-cols-3' => $aside && $headingVisible,
            'bg-white shadow-sm rounded-xl ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10' => !$aside,
        ]) }}>

    @if ($headingVisible)
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
    @endif

    @if ($aside)
        <div
            class="p-6 bg-white shadow-sm fi-timeline-section-content md:col-span-2 rounded-xl ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
            {{ $slot }}
        </div>
    @else
        <div @class([
            'p-6 fi-timeline-section-content',
            'border-t border-gray-200 dark:border-white/10' => $headingVisible,
        ])>
            {{ $slot }}
        </div>
    @endif
</section>
