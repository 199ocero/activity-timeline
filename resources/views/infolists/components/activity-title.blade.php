<h3 class="text-base font-semibold text-gray-800 dark:text-white">
    @if($isHtmlAllowed)
        {!! $getState() ?? $getPlaceholder() !!}
    @else
        {{ $getState() ?? $getPlaceholder() }}
    @endif
</h3>
