@props([
    'status',
])

@if ($status)
    <div
        x-data="{}"
        x-init="window.dispatchEvent(new CustomEvent('toast', { detail: { type: 'success', message: @js($status) } }))"
        class="hidden"
        aria-hidden="true"
    ></div>
@endif
