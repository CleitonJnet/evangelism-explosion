@props([
    'createRoute' => null,
    'editRoute' => null,
    'deleteRoute' => null,
    'listRoute' => null,
])

<div class="flex flex-wrap items-center gap-2">
    @if ($listRoute)
        <x-src.toolbar.button :href="$listRoute" :label="__('Listar')" icon="list" :tooltip="__('Listar tudo')" />
    @endif

    @if ($createRoute)
        <x-src.toolbar.button :href="$createRoute" :label="__('Novo')" icon="plus" :tooltip="__('Adicionar registro')" />
    @endif

    @if ($editRoute)
        <x-src.toolbar.button :href="$editRoute" :label="__('Editar')" icon="pencil" :tooltip="__('Editar registro')" />
    @endif

    @if ($deleteRoute)
        <x-src.toolbar.button :href="$deleteRoute" :label="__('Excluir')" icon="trash" :tooltip="__('Deletar registro')" />
    @endif
</div>
