<div class="space-y-8">
    <div>
        <div class="text-lg font-bold uppercase">{{ $course->type }}: {{ $course->name }}:</div>
        <div class="border-b-4 border-amber-800 pb-4">
            <div class="grid grid-cols-12 gap-4 even:bg-slate-50 odd:bg-slate-100 px-1">
                <div class="col-span-2 font-semibold">{{ __('Banner') }}:</div>
                <div class="col-span-10">{{ $course->banner }}</div>
            </div>
            <div class="grid grid-cols-12 gap-4 even:bg-slate-50 odd:bg-slate-100 px-1">
                <div class="col-span-2 font-semibold">{{ __('Logo') }}:</div>
                <div class="col-span-10">{{ $course->logo }}</div>
            </div>
            <div class="grid grid-cols-12 gap-4 even:bg-slate-50 odd:bg-slate-100 px-1">
                <div class="col-span-2 font-semibold">{{ __('Iniciais') }}:</div>
                <div class="col-span-10">{{ $course->initials }}</div>
            </div>
            <div class="grid grid-cols-12 gap-4 even:bg-slate-50 odd:bg-slate-100 px-1">
                <div class="col-span-2 font-semibold">{{ __('Target Audience') }}:</div>
                <div class="col-span-10">{{ $course->targetAudience }}</div>
            </div>
            <div class="grid grid-cols-12 gap-4 even:bg-slate-50 odd:bg-slate-100 px-1">
                <div class="col-span-2 font-semibold">{{ __('Learn More Link') }}:</div>
                <div class="col-span-10">{{ $course->learnMoreLink }}</div>
            </div>
            <div class="grid grid-cols-12 gap-4 even:bg-slate-50 odd:bg-slate-100 px-1">
                <div class="col-span-2 font-semibold">{{ __('Suggested price') }}:</div>
                <div class="col-span-10">{{ $course->price }}</div>
            </div>
            <div class="grid grid-cols-12 gap-4 even:bg-slate-50 odd:bg-slate-100 px-1">
                <div class="col-span-2 font-semibold">{{ __('Color') }}:</div>
                <div class="col-span-10">{{ $course->color }}</div>
            </div>
            <div class="grid grid-cols-12 gap-4 even:bg-slate-50 odd:bg-slate-100 px-1">
                <div class="col-span-2 font-semibold">{{ __('Certificate') }}:</div>
                <div class="col-span-10">{{ $course->certificate }}</div>
            </div>
            <div class="grid grid-cols-12 gap-4 even:bg-slate-50 odd:bg-slate-100 px-1">
                <div class="col-span-2 font-semibold">{{ __('Know How') }}:</div>
                <div class="col-span-10">{{ $course->knowhow }}</div>
            </div>
            <div class="grid grid-cols-12 gap-4 even:bg-slate-50 odd:bg-slate-100 px-1">
                <div class="col-span-2 font-semibold">{{ __('Description') }}:</div>
                <div class="col-span-10">{{ $course->description }}</div>
            </div>
        </div>
    </div>

    <div class="grid gap-6 lg:grid-cols-2">
        <section class="rounded-2xl border border-[color:var(--ee-app-border)] bg-[color:var(--ee-app-surface)] p-6">
            <div class="flex items-center justify-between gap-4">
                <div>
                    <flux:heading size="sm" level="2">{{ __('Unidades do curso') }}</flux:heading>
                    <flux:text class="text-sm text-[color:var(--ee-app-muted)]">
                        {{ __('Gerencie as unidades do manual de treinamento.') }}
                    </flux:text>
                </div>
                <flux:button variant="primary" wire:click="openCreateSectionModal">
                    {{ __('Adicionar unidade') }}
                </flux:button>
            </div>

            <div class="mt-6 overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="text-xs uppercase text-[color:var(--ee-app-muted)]">
                        <tr class="border-b border-[color:var(--ee-app-border)]">
                            <th class="px-3 py-2">{{ __('Ordem') }}</th>
                            <th class="px-3 py-2">{{ __('Unidade') }}</th>
                            <th class="px-3 py-2">{{ __('Duração') }}</th>
                            <th class="px-3 py-2">{{ __('Ações') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[color:var(--ee-app-border)]">
                        @forelse ($sections as $section)
                            <tr wire:key="section-{{ $section->id }}">
                                <td class="px-3 py-2">{{ $section->order ?? '-' }}</td>
                                <td class="px-3 py-2">
                                    <div class="font-semibold">{{ $section->name }}</div>
                                    <div class="text-xs text-[color:var(--ee-app-muted)]">
                                        {{ $section->devotional ?? __('Sem devocional') }}
                                    </div>
                                </td>
                                <td class="px-3 py-2">{{ $section->duration ?? '-' }}</td>
                                <td class="px-3 py-2">
                                    <div class="flex flex-wrap gap-2">
                                        <flux:button variant="outline"
                                            wire:click="openEditSectionModal({{ $section->id }})">
                                            {{ __('Editar') }}
                                        </flux:button>
                                        <flux:button variant="danger"
                                            wire:click="openDeleteSectionModal({{ $section->id }})">
                                            {{ __('Remover') }}
                                        </flux:button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td class="px-3 py-4 text-sm text-[color:var(--ee-app-muted)]" colspan="4">
                                    {{ __('Nenhuma unidade cadastrada.') }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        <section class="rounded-2xl border border-[color:var(--ee-app-border)] bg-[color:var(--ee-app-surface)] p-6">
            <div class="flex items-center justify-between gap-4">
                <div>
                    <flux:heading size="sm" level="2">{{ __('Professores do curso') }}</flux:heading>
                    <flux:text class="text-sm text-[color:var(--ee-app-muted)]">
                        {{ __('Associe professores responsáveis por este curso.') }}
                    </flux:text>
                </div>
                <flux:button variant="primary" wire:click="openCreateTeacherModal">
                    {{ __('Adicionar professor') }}
                </flux:button>
            </div>

            <div class="mt-6 overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="text-xs uppercase text-[color:var(--ee-app-muted)]">
                        <tr class="border-b border-[color:var(--ee-app-border)]">
                            <th class="px-3 py-2">{{ __('Professor') }}</th>
                            <th class="px-3 py-2">{{ __('Status') }}</th>
                            <th class="px-3 py-2">{{ __('Ações') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[color:var(--ee-app-border)]">
                        @forelse ($teachers as $teacher)
                            <tr wire:key="teacher-{{ $teacher->id }}">
                                <td class="px-3 py-2">
                                    @if ($teacher->church_id)
                                        <a class="font-semibold"
                                            href="{{ route('app.director.church.profile.show', ['church' => $teacher->church_id, 'profile' => $teacher->id]) }}">
                                            {{ $teacher->name }}
                                        </a>
                                    @else
                                        <span class="font-semibold">{{ $teacher->name }}</span>
                                    @endif
                                    <div class="text-xs text-[color:var(--ee-app-muted)]">{{ $teacher->email }}</div>
                                </td>
                                <td class="px-3 py-2">
                                    {{ (int) ($teacher->pivot->status ?? 0) === 1 ? __('Ativo') : __('Inativo') }}
                                </td>
                                <td class="px-3 py-2">
                                    <div class="flex flex-wrap gap-2">
                                        <flux:button variant="outline"
                                            wire:click="openEditTeacherModal({{ $teacher->id }})">
                                            {{ __('Editar') }}
                                        </flux:button>
                                        <flux:button variant="danger"
                                            wire:click="openDeleteTeacherModal({{ $teacher->id }})">
                                            {{ __('Remover') }}
                                        </flux:button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td class="px-3 py-4 text-sm text-[color:var(--ee-app-muted)]" colspan="3">
                                    {{ __('Nenhum professor associado.') }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>

    <flux:modal name="section-modal" wire:model="showSectionModal" class="max-w-2xl">
        <form class="space-y-6" wire:submit="saveSection">
            <div>
                <flux:heading size="lg">
                    {{ $editingSectionId ? __('Atualizar unidade') : __('Adicionar unidade') }}
                </flux:heading>
                <flux:subheading>
                    {{ __('Preencha os dados da unidade do manual de treinamento.') }}
                </flux:subheading>
            </div>

            <div class="grid gap-4 md:grid-cols-2">
                <flux:input wire:model="sectionForm.name" :label="__('Nome da unidade')" required autofocus />
                <flux:input wire:model="sectionForm.order" :label="__('Ordem')" type="number" min="0" />
                <flux:input wire:model="sectionForm.duration" :label="__('Duração')" />
                <flux:input wire:model="sectionForm.devotional" :label="__('Devocional')" />
                <flux:input wire:model="sectionForm.banner" :label="__('Banner')" />
            </div>

            <div class="grid gap-4">
                <flux:textarea wire:model="sectionForm.description" :label="__('Descrição')" rows="3" />
                <flux:textarea wire:model="sectionForm.knowhow" :label="__('Conhecimento')" rows="3" />
            </div>

            @error('sectionForm.name')
                <flux:text class="text-sm text-red-600 dark:text-red-400">{{ $message }}</flux:text>
            @enderror

            <div class="flex flex-wrap justify-end gap-2">
                <flux:button type="button" variant="ghost" wire:click="closeSectionModal">
                    {{ __('Cancelar') }}
                </flux:button>
                <flux:button type="submit" variant="primary" wire:loading.attr="disabled"
                    wire:target="saveSection">
                    {{ __('Salvar') }}
                </flux:button>
            </div>
        </form>
    </flux:modal>

    <flux:modal name="teacher-modal" wire:model="showTeacherModal" class="max-w-lg">
        <form class="space-y-6" wire:submit="saveTeacher">
            <div>
                <flux:heading size="lg">
                    {{ $editingTeacherId ? __('Atualizar professor') : __('Adicionar professor') }}
                </flux:heading>
                <flux:subheading>
                    {{ __('Associe professores ao curso e ajuste o status.') }}
                </flux:subheading>
                <flux:text class="mt-2 text-sm text-[color:var(--ee-app-muted)]">
                    {{ __('Para adicionar professores, deve primeiro atribuir a função Professor ao usuário no setup do sistema.') }}
                </flux:text>
            </div>

            @if ($teacherAlreadyAssignedWarning)
                <flux:callout variant="warning" icon="exclamation-triangle"
                    heading="{{ __('Não foi possível salvar o professor.') }}">
                    {{ __('Este professor já está na lista de professores.') }}
                </flux:callout>
            @endif

            <div class="grid gap-4">
                @if ($editingTeacherId)
                    <input type="hidden" wire:model="teacherForm.user_id" />
                    <div
                        class="rounded-xl border border-[color:var(--ee-app-border)] bg-[color:var(--ee-app-surface)] p-4 text-sm text-[color:var(--ee-app-muted)]">
                        <div class="font-semibold text-[color:var(--ee-app-text)]">{{ $editingTeacherName }}</div>
                        <div>{{ __('Professor selecionado') }}</div>
                    </div>
                @else
                    <flux:input wire:model.live.debounce.300ms="teacherSearch" :label="__('Buscar professor')"
                        :placeholder="__('Digite o nome ou e-mail')" autofocus />
                    <flux:select wire:model="teacherForm.user_id" :label="__('Professor')"
                        :placeholder="__('Selecione um professor')">
                        @forelse ($teacherCandidates as $teacher)
                            @php
                                $isAssigned = in_array($teacher->id, $assignedTeacherIds, true);
                            @endphp
                            <option value="{{ $teacher->id }}" @if ($isAssigned) disabled @endif>
                                {{ $teacher->name }} ({{ $teacher->email }})
                                @if ($isAssigned)
                                    - {{ __('Já selecionado') }}
                                @endif
                            </option>
                        @empty
                            <option value="" disabled>{{ __('Nenhum professor disponível.') }}</option>
                        @endforelse
                    </flux:select>
                    <flux:text class="text-xs text-[color:var(--ee-app-muted)]">
                        {{ __('Mostrando até 15 resultados.') }}
                    </flux:text>
                    <flux:text class="text-xs text-[color:var(--ee-app-muted)]">
                        {{ __('Professores já associados aparecem desabilitados.') }}
                    </flux:text>
                    @php
                        $selectedTeacherId = $teacherForm['user_id'] ?? null;
                        $selectedAlreadyAssigned = $selectedTeacherId
                            && in_array($selectedTeacherId, $assignedTeacherIds, true);
                    @endphp
                    @if ($selectedAlreadyAssigned)
                        <flux:text class="text-sm text-amber-700 dark:text-amber-400">
                            {{ __('Este professor já está na lista de professores.') }}
                        </flux:text>
                    @endif
                @endif

                <flux:select wire:model="teacherForm.status" :label="__('Status')" :autofocus="$editingTeacherId">
                    <option value="1">{{ __('Ativo') }}</option>
                    <option value="0">{{ __('Inativo') }}</option>
                </flux:select>

                @error('teacherForm.user_id')
                    <flux:text class="text-sm text-red-600 dark:text-red-400">{{ $message }}</flux:text>
                @enderror
                @error('teacherForm.status')
                    <flux:text class="text-sm text-red-600 dark:text-red-400">{{ $message }}</flux:text>
                @enderror
            </div>

            <div class="flex flex-wrap justify-end gap-2">
                @php
                    $selectedTeacherId = $teacherForm['user_id'] ?? null;
                    $selectedAlreadyAssigned = $selectedTeacherId
                        && in_array($selectedTeacherId, $assignedTeacherIds, true);
                @endphp
                <flux:button type="button" variant="ghost" wire:click="closeTeacherModal">
                    {{ __('Cancelar') }}
                </flux:button>
                <flux:button type="submit" variant="primary" wire:loading.attr="disabled"
                    wire:target="saveTeacher" :disabled="$selectedAlreadyAssigned">
                    {{ __('Salvar') }}
                </flux:button>
            </div>
        </form>
    </flux:modal>

    <flux:modal name="delete-section-modal" wire:model="showDeleteSectionModal" class="max-w-md">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">{{ __('Remover unidade') }}</flux:heading>
                <flux:subheading>
                    {{ __('Esta ação é permanente. Deseja continuar?') }}
                </flux:subheading>
            </div>

            <div class="flex flex-wrap justify-end gap-2">
                <flux:button variant="ghost" wire:click="closeDeleteSectionModal">
                    {{ __('Cancelar') }}
                </flux:button>
                <flux:button variant="danger" wire:click="confirmDeleteSection">
                    {{ __('Remover') }}
                </flux:button>
            </div>
        </div>
    </flux:modal>

    <flux:modal name="delete-teacher-modal" wire:model="showDeleteTeacherModal" class="max-w-md">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">{{ __('Remover professor') }}</flux:heading>
                <flux:subheading>
                    {{ __('Esta ação é permanente. Deseja continuar?') }}
                </flux:subheading>
            </div>

            <div class="flex flex-wrap justify-end gap-2">
                <flux:button variant="ghost" wire:click="closeDeleteTeacherModal">
                    {{ __('Cancelar') }}
                </flux:button>
                <flux:button variant="danger" wire:click="confirmDeleteTeacher">
                    {{ __('Remover') }}
                </flux:button>
            </div>
        </div>
    </flux:modal>
</div>
