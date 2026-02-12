@if ($showChurchModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/70 px-4 py-8">
        <div class="w-full max-w-3xl overflow-hidden rounded-3xl bg-white shadow-xl">
            <div class="flex items-start justify-between gap-4 border-b border-slate-200 bg-slate-50 px-6 py-5">
                <div>
                    <h3 class="text-lg font-extrabold text-slate-900">Selecione sua igreja</h3>
                    <p class="mt-1 text-sm text-slate-600">
                        Para concluir sua inscrição, precisamos vincular sua igreja. Se não encontrar, cadastre abaixo
                        para análise do professor.
                    </p>
                </div>
                <button type="button" wire:click="closeChurchModal"
                    class="rounded-full border border-slate-200 bg-white px-3 py-1 text-xs font-semibold text-slate-500 hover:text-slate-700">
                    Fechar
                </button>
            </div>

            <div class="max-h-[70vh] space-y-6 overflow-y-auto px-6 py-6">
                @if (!$showChurchTempForm)
                    <form wire:submit="saveChurchSelection" class="space-y-6">
                        <div class="flex flex-wrap gap-4">
                            <x-src.form.input name="churchSearch" wire:model.live.debounce.300ms="churchSearch"
                                label="Buscar sua igreja" width_basic="260" />

                            <x-src.form.select name="selectedChurchId" wire:model.live="selectedChurchId"
                                :value="$selectedChurchId" label="Igreja" width_basic="320" :select="false"
                                wire:key="church-select-{{ md5($churchSearch) }}" :options="$this->churches
                                    ->map(
                                        fn($church) => [
                                            'value' => $church->id,
                                            'label' => $church->name . ' • ' . $church->city . ' - ' . $church->state,
                                        ],
                                    )
                                    ->values()
                                    ->all()" />
                        </div>

                        @if ($this->churches->isEmpty())
                            <div class="rounded-2xl border border-amber-200 bg-amber-50/70 p-4 text-sm text-amber-900">
                                Não encontramos nenhuma igreja com esse filtro. Você pode cadastrar sua igreja para a
                                equipe oficial analisar.
                            </div>
                        @endif

                        <div class="flex flex-wrap items-center justify-between gap-3">
                            <button type="button" wire:click="startChurchTempRegistration"
                                class="text-sm font-semibold text-sky-800 hover:underline">
                                Não encontrei minha igreja
                            </button>

                            <div class="flex items-center gap-3">
                                <x-src.btn-silver label="Cancelar" type="button" wire:click="closeChurchModal"
                                    class="px-4 py-2" />
                                <x-src.btn-gold label="Salvar e continuar" type="submit" class="px-4 py-2" />
                            </div>
                        </div>
                    </form>
                @else
                    <form wire:submit="saveChurchTemp" class="space-y-6">
                        <div class="grid gap-4 sm:grid-cols-2">
                            <x-src.form.input name="churchTempName" wire:model.defer="churchTempName"
                                label="Nome completo da Igreja" width_basic="260" required />

                            <x-src.form.input name="churchTempPastor" wire:model.defer="churchTempPastor"
                                label="Nome do pastor titular" width_basic="260" />

                            <x-src.form.input type="tel" name="churchTempPhone" wire:model.defer="churchTempPhone"
                                label="Telefone &#10023; WhatsApp" width_basic="220" />

                            <x-src.form.input type="email" name="churchTempEmail" wire:model.defer="churchTempEmail"
                                label="E-mail da Igreja" width_basic="260" />

                            <x-src.form.input name="churchTempStreet" wire:model.defer="churchTempStreet"
                                label="Logradouro" width_basic="260" />

                            <x-src.form.input name="churchTempNumber" wire:model.defer="churchTempNumber" label="Numero"
                                width_basic="120" />

                            <x-src.form.input name="churchTempComplement" wire:model.defer="churchTempComplement"
                                label="Complemento" width_basic="200" />

                            <x-src.form.input name="churchTempDistrict" wire:model.defer="churchTempDistrict"
                                label="Bairro" width_basic="200" />

                            <x-src.form.input name="churchTempCity" wire:model.defer="churchTempCity" label="Cidade"
                                width_basic="200" required />

                            <x-src.form.input name="churchTempState" wire:model.defer="churchTempState" label="UF"
                                width_basic="80" required />

                            <x-src.form.input name="churchTempPostalCode" wire:model.defer="churchTempPostalCode"
                                label="CEP" width_basic="140" />

                            <x-src.form.input name="churchTempContact" wire:model.defer="churchTempContact"
                                label="Nome completo do Contato" width_basic="260" />

                            <x-src.form.input name="churchTempContactPhone" wire:model.defer="churchTempContactPhone"
                                label="Telefone do Contato" width_basic="220" />

                            <x-src.form.input name="churchTempContactEmail" wire:model.defer="churchTempContactEmail"
                                type="email" label="E-mail do Contato" width_basic="260" />
                        </div>

                        <x-src.form.textarea name="churchTempNotes" wire:model.defer="churchTempNotes"
                            label="Observações para o professor" rows="2" />

                        <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4 text-sm text-slate-600">
                            A equipe do evento verá este cadastro em "igrejas temporárias" e fará a validação antes de
                            incluir oficialmente na base.
                        </div>

                        <div class="flex flex-wrap items-center justify-between gap-3">
                            <button type="button" wire:click="backToChurchSearch"
                                class="text-sm font-semibold text-sky-800 hover:underline">
                                Voltar para a busca
                            </button>

                            <div class="flex items-center gap-3">
                                <x-src.btn-silver label="Cancelar" type="button" wire:click="closeChurchModal"
                                    class="px-4 py-2" />
                                <x-src.btn-gold label="Enviar cadastro" type="submit" class="px-4 py-2" />
                            </div>
                        </div>
                    </form>
                @endif
            </div>
        </div>
    </div>
@endif
