<h3 class="text-base text-gray-800 dark:text-white">
    @if ($isHtmlAllowed())
        {!! $getModifiedState() ?? (!is_array($getState()) ? $getState() ?? $getPlaceholder() : null) !!}
    @else
        {{ $getModifiedState() ?? (!is_array($getState()) ? $getState() ?? $getPlaceholder() : null) }}
    @endif
</h3>
