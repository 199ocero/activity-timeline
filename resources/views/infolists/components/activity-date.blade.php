@php
    $tooltip = $getTooltip();
@endphp

<span
    @if (filled($tooltip))
        x-data="{}"
        x-tooltip="{
                content: @js($tooltip),
                theme: $store.theme,
            }"
    @endif
    class="flex-shrink-0 text-xs font-medium text-gray-500 uppercase fi-timeline-item-date dark:text-gray-400">
    {{ $getState() != null ? $getDate($getState()) : $getPlaceholder() }}
</span>
