<x-mail::message>
    # Olá, {{ $mentorUser->name }}!

    Sua participação como mentor foi confirmada no treinamento:
    **{{ $training->course?->name ?? 'Treinamento' }}**.

    Para acessar o sistema com segurança, use o link abaixo e defina uma nova senha.
    Esse passo é obrigatório no primeiro acesso.

    <x-mail::button :url="$passwordResetUrl">
        Trocar senha agora
    </x-mail::button>

    Obrigado,<br>
    {{ config('app.name') }}
</x-mail::message>
