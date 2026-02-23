<x-app.layouts.auth>
    <div class="flex flex-col gap-6">
        <x-app.auth-header :title="__('Troca de senha obrigatória')" :description="__('Defina uma nova senha para continuar acessando o sistema.')" />

        <form method="POST" action="{{ route('force-password-change.update') }}" class="flex flex-col gap-6">
            @csrf
            @method('PUT')

            <flux:input name="password" :label="__('Nova senha')" type="password" required autocomplete="new-password"
                :placeholder="__('Nova senha')" viewable />

            <flux:input name="password_confirmation" :label="__('Confirmar nova senha')" type="password" required
                autocomplete="new-password" :placeholder="__('Confirmar nova senha')" viewable />

            <div class="flex items-center justify-end">
                <flux:button type="submit" variant="primary" class="w-full">
                    {{ __('Salvar nova senha') }}
                </flux:button>
            </div>
        </form>

        <form method="POST" action="{{ route('logout') }}" class="flex justify-center">
            @csrf
            <flux:button type="submit" variant="ghost">
                {{ __('Sair') }}
            </flux:button>
        </form>
    </div>
</x-app.layouts.auth>
