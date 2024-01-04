<div class="my-4 first:mt-0">
    <p class="text-xs font-medium text-gray-500 uppercase dark:text-gray-400">
        {{ $getState() != null ? $getDate($getState()) : $getPlaceholder() }}
    </p>
</div>
