<span class="flex-shrink-0 text-xs font-medium text-gray-500 uppercase fi-timeline-item-date dark:text-gray-400">
    @if ($isHtmlAllowed())
        {!! $getModifiedState() ?? (!is_array($getState()) ? $getState() ?? $getPlaceholder() : null) !!}
    @else
        {{ $getModifiedState() ?? (!is_array($getState()) ? $getState() ?? $getPlaceholder() : null) }}
    @endif
</span>
