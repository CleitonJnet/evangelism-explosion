@props([
    'on',
])

<div
    x-data="{}"
    x-init="@this.on('{{ $on }}', () => window.dispatchEvent(new CustomEvent('toast', { detail: { type: 'success', message: @js($slot->isEmpty() ? __('Saved.') : (string) $slot) } })))"
    class="hidden"
    aria-hidden="true"
></div>
