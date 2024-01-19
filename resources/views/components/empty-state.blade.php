@props([
    'description' => null,
    'heading',
    'icon',
])

<div {{ $attributes->class(['fi-timeline-empty-state px-6 py-12']) }}>
    <div class="grid max-w-lg mx-auto text-center fi-timeline-empty-state-content justify-items-center">
        <div class="p-3 mb-4 bg-gray-100 rounded-full fi-timeline-empty-state-icon-ctn dark:bg-gray-500/20">
            <x-filament::icon :icon="$icon"
                class="w-6 h-6 text-gray-500 fi-timeline-empty-state-icon dark:text-gray-400" />
        </div>

        <h4
            {{ $attributes->class(['fi-timeline-empty-state-heading text-base font-semibold leading-6 text-gray-950 dark:text-white']) }}>
            {{ $heading }}
        </h4>

        @if ($description)
            <p
                {{ $attributes->class(['fi-timeline-empty-state-description text-sm text-gray-500 dark:text-gray-400']) }}>
                {{ $description }}
            </p>
        @endif
    </div>
</div>
