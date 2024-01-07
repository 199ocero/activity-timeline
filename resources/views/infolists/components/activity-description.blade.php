<p class="text-sm text-gray-600 dark:text-gray-400">
    @if($isHtmlAllowed)
        {!! $getState() ?? $getPlaceholder() !!}
    @else
        {{ $getState() ?? $getPlaceholder() }}
    @endif
</p>
