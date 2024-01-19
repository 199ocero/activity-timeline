<p class="text-sm text-gray-600 fi-timeline-item-description dark:text-gray-400">
    @if ($isHtmlAllowed())
        {!! $getModifiedState() ?? (!is_array($getState()) ? $getState() ?? $getPlaceholder() : null) !!}
    @else
        {{ $getModifiedState() ?? (!is_array($getState()) ? $getState() ?? $getPlaceholder() : null) }}
    @endif
</p>
