<div class="rounded-2xl border border-slate-800/40 bg-white/90 p-4 basis-64 flex-auto">
    @php
        $resolveTeacherPhotoUrl = static function (?string $profilePhotoPath): string {
            $defaultPhotoUrl = asset('images/profile.webp');
            $normalizedPath = trim((string) $profilePhotoPath);

            if ($normalizedPath === '') {
                return $defaultPhotoUrl;
            }

            $normalizedPath = ltrim($normalizedPath, '/');

            if (! \Illuminate\Support\Facades\Storage::disk('public')->exists($normalizedPath)) {
                return $defaultPhotoUrl;
            }

            return asset('storage/' . $normalizedPath);
        };
    @endphp

    <div class="flex items-center justify-between gap-3 border-b-2 border-sky-800/30 pb-2 mb-2">
        <h4 class="text-sm font-semibold text-slate-900 uppercase">
            {{ $isPluralTitle ? __('Professores') : __('Professor') }}
        </h4>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full table-auto text-left text-sm">
            <tbody>
                @php
                    $principalTeacher = $training->teacher;
                    $assistantTeachers = $training->assistantTeachers;
                @endphp

                @if (!$principalTeacher && $assistantTeachers->isEmpty())
                    <tr>
                        <td colspan="2" class="px-3 py-3 text-sm text-slate-500">
                            {{ __('Nenhum professor vinculado ao evento.') }}
                        </td>
                    </tr>
                @else
                    @if ($principalTeacher)
                        @php
                            $principalPhotoUrl = $resolveTeacherPhotoUrl($principalTeacher->profile_photo_path);
                        @endphp
                        <tr>
                            <td colspan="2" class="px-3">
                                <div class="text-[11px] font-semibold uppercase tracking-wide text-slate-500">
                                    {{ __('Professor principal') }}
                                </div>
                            </td>
                        </tr>

                        <tr class="bg-slate-100/80 transition-colors duration-200 hover:bg-slate-200/70"
                            wire:key="training-principal-teacher-row-{{ $principalTeacher->id }}">
                            <td class="w-16 px-3 py-3 align-middle">
                                <img src="{{ $principalPhotoUrl }}" alt="{{ $principalTeacher->name }}"
                                    class="h-10 w-10 rounded-full object-cover border border-slate-300"
                                    onerror="this.onerror=null;this.src='{{ asset('images/profile.webp') }}';">
                            </td>
                            <td class="px-3 py-3 align-middle">
                                <div class="font-semibold text-slate-900">{{ $principalTeacher->name }}</div>
                                <div class="text-xs text-slate-500">{{ $principalTeacher->email }}</div>
                            </td>
                        </tr>
                    @endif

                    <tr>
                        <td colspan="2" class="px-3 pt-1 pb-0.5">
                            <div class="border-t border-slate-200"></div>
                        </td>
                    </tr>

                    <tr>
                        <td colspan="2" class="px-3">
                            <div class="text-[11px] font-semibold uppercase tracking-wide text-slate-500">
                                {{ __('Professores auxiliares') }}
                            </div>
                        </td>
                    </tr>

                    @forelse ($assistantTeachers as $assistantTeacher)
                        @php
                            $assistantPhotoUrl = $resolveTeacherPhotoUrl($assistantTeacher->profile_photo_path);
                        @endphp
                        <tr class="odd:bg-slate-100/80 even:bg-slate-50/60 transition-colors duration-200 hover:bg-slate-200/70"
                            wire:key="training-assistant-teacher-row-{{ $assistantTeacher->id }}">
                            <td class="w-16 px-3 py-1.5 align-middle">
                                <img src="{{ $assistantPhotoUrl }}" alt="{{ $assistantTeacher->name }}"
                                    class="h-10 w-10 rounded-full object-cover border border-slate-300"
                                    onerror="this.onerror=null;this.src='{{ asset('images/profile.webp') }}';">
                            </td>
                            <td class="px-3 py-1.5 align-middle">
                                <div class="font-semibold text-slate-900">{{ $assistantTeacher->name }}</div>
                                <div class="text-xs text-slate-500">{{ $assistantTeacher->email }}</div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="2" class="px-3 py-1.5 text-xs text-slate-500">
                                {{ __('Nenhum professor auxiliar vinculado.') }}
                            </td>
                        </tr>
                    @endforelse
                @endif
            </tbody>
        </table>
    </div>

    <flux:modal name="manage-training-teachers-modal" wire:model="showModal"
        class="max-w-5xl w-[calc(100%-4px)] mx-auto bg-sky-900! p-0! max-h-[calc(100vh-4px)]! overflow-hidden max-h-[calc(100vh-50px)]! overflow-hidden">
        <form class="flex max-h-[calc(100vh-50px)] flex-col overflow-hidden" wire:submit="requestSave">
            <div class="shrink-0 border-b border-sky-700 px-6 py-4">
                <flux:heading size="lg"><span class="text-white">{{ __('Professores do treinamento') }}</span>
                </flux:heading>
                <flux:subheading>
                    <span class="text-white/90">
                        {{ __('Atualize o professor titular e a lista de professores auxiliares.') }}
                    </span>
                </flux:subheading>
            </div>

            <div class="min-h-0 flex-1 overflow-y-auto bg-white/90 space-y-4 px-6 py-4">
                <div class="rounded-xl border border-sky-500 bg-sky-100 p-4 space-y-3">
                    <label class="block text-xs font-semibold uppercase text-slate-700">
                        {{ __('Professor titular') }}
                    </label>
                    <select wire:model="teacherId" wire:change="requestPrincipalTeacherChange($event.target.value)"
                        class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-800">
                        <option value="">{{ __('Selecione um professor titular') }}</option>
                        @foreach ($principalTeacherCandidates as $candidate)
                            <option value="{{ $candidate->id }}">
                                {{ $candidate->name }} ({{ $candidate->email }})
                            </option>
                        @endforeach
                    </select>
                    @error('teacherId')
                        <div class="text-xs text-red-600">{{ $message }}</div>
                    @enderror
                </div>

                <div class="rounded-xl border border-amber-500 bg-amber-100 p-4 space-y-3">
                    <label class="block text-xs font-semibold uppercase text-slate-700">
                        {{ __('Professores auxiliares') }}
                    </label>
                    <div class="relative">
                        <input type="text" wire:model.live.debounce.300ms="assistantSearch"
                            placeholder="{{ __('Filtrar por nome ou e-mail') }}"
                            class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-800" />

                        @if (trim($assistantSearch) !== '')
                            <div
                                class="absolute z-20 mt-1 w-full overflow-hidden rounded-lg border border-slate-200 bg-white shadow-lg">
                                <div class="max-h-52 overflow-y-auto divide-y divide-slate-100">
                                    @forelse ($assistantTeacherSearchResults as $candidate)
                                        <button type="button"
                                            class="w-full cursor-pointer px-3 py-2 text-left text-sm text-slate-700 odd:bg-slate-100/80 even:bg-slate-50/60 hover:bg-slate-200/70 transition-colors duration-200"
                                            wire:click="addAssistantTeacher({{ $candidate->id }})">
                                            <div class="font-semibold">{{ $candidate->name }}</div>
                                            <div class="text-xs text-slate-500">{{ $candidate->email }}</div>
                                        </button>
                                    @empty
                                        <div class="px-3 py-2 text-xs text-slate-500">
                                            {{ __('Nenhum professor encontrado para o filtro informado.') }}
                                        </div>
                                    @endforelse
                                </div>
                            </div>
                        @endif
                    </div>

                    <div class="rounded-lg border border-slate-200 bg-white overflow-x-auto">
                        <table class="w-full text-left text-sm">
                            <tbody>
                                @forelse ($selectedAssistantTeachers as $assistantTeacher)
                                    @php
                                        $assistantPhotoUrl = $resolveTeacherPhotoUrl(
                                            $assistantTeacher->profile_photo_path,
                                        );
                                    @endphp
                                    <tr class="odd:bg-slate-100/80 even:bg-slate-50/60 transition-colors duration-200 hover:bg-slate-200/70"
                                        wire:key="selected-assistant-teacher-{{ $assistantTeacher->id }}">
                                        <td class="w-16 px-3 py-3 align-middle">
                                            <img src="{{ $assistantPhotoUrl }}" alt="{{ $assistantTeacher->name }}"
                                                class="h-10 w-10 rounded-full border border-slate-300 object-cover"
                                                onerror="this.onerror=null;this.src='{{ asset('images/profile.webp') }}';">
                                        </td>
                                        <td class="px-3 py-3 align-middle">
                                            <div class="font-semibold text-slate-900">{{ $assistantTeacher->name }}
                                            </div>
                                            <div class="text-xs text-slate-500">{{ $assistantTeacher->email }}</div>
                                        </td>
                                        <td class="w-14 px-3 py-3 align-middle text-right">
                                            <flux:button variant="danger" size="sm" icon="trash"
                                                icon:variant="outline"
                                                wire:click="removeAssistantTeacher({{ $assistantTeacher->id }})"
                                                aria-label="{{ __('Remover professor auxiliar') }}" />
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="px-3 py-3 text-xs text-slate-500">
                                            {{ __('Nenhum professor auxiliar selecionado.') }}
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @error('assistantTeacherIds')
                        <div class="text-xs text-red-600">{{ $message }}</div>
                    @enderror
                    @error('assistantTeacherIds.*')
                        <div class="text-xs text-red-600">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="shrink-0 border-t border-sky-700 px-6 py-4 flex justify-end gap-2 bg-white/95">
                <x-src.btn-silver type="button" wire:click="closeModal" wire:loading.attr="disabled"
                    wire:target="requestSave,confirmPrincipalTeacherChange">
                    {{ __('Cancelar') }}
                </x-src.btn-silver>
                <x-src.btn-gold type="submit" wire:loading.attr="disabled"
                    wire:target="requestSave,confirmPrincipalTeacherChange">
                    {{ __('Salvar') }}
                </x-src.btn-gold>
            </div>
        </form>
    </flux:modal>

    <flux:modal name="confirm-principal-teacher-change-modal" wire:model="showPrincipalChangeConfirmation"
        class="max-w-md">
        <div class="space-y-4">
            <div>
                <flux:heading size="lg">{{ __('Confirmar troca de professor titular') }}</flux:heading>
                <flux:subheading>
                    {{ __('Ao confirmar esta alteração e salvar, você não terá mais acesso a este treinamento.') }}
                </flux:subheading>
            </div>

            <div class="flex items-center justify-end gap-2">
                <x-src.btn-silver type="button" wire:click="cancelPrincipalTeacherChange">
                    {{ __('Cancelar') }}
                </x-src.btn-silver>
                <x-src.btn-gold type="button" wire:click="confirmPrincipalTeacherChange"
                    wire:loading.attr="disabled" wire:target="confirmPrincipalTeacherChange">
                    {{ __('Confirmar') }}
                </x-src.btn-gold>
            </div>
        </div>
    </flux:modal>
</div>
