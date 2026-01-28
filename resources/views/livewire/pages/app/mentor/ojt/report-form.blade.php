<form wire:submit.prevent="saveDraft" class="space-y-8">
    <section class="rounded-2xl border border-[color:var(--ee-app-border)] bg-white p-6">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <flux:heading size="sm" level="2">{{ __('OJT Report') }}</flux:heading>
                <flux:text class="text-sm text-[color:var(--ee-app-muted)]">
                    {{ __('Team') }} {{ $team->team_number }} · {{ __('Week') }} {{ $team->session?->week_number }}
                </flux:text>
                @if ($submittedAt)
                    <flux:text class="text-xs text-[color:var(--ee-app-muted)]">
                        {{ __('Submitted at') }}: {{ $submittedAt }}
                    </flux:text>
                @endif
            </div>
        </div>

        @if ($isLocked)
            <div class="mt-4">
                <flux:callout variant="warning" icon="lock-closed">
                    <div class="text-sm">
                        {{ __('This report is locked after submission. Ask your teacher to unlock if edits are needed.') }}
                    </div>
                </flux:callout>
            </div>
        @endif
    </section>

    <section class="rounded-2xl border border-[color:var(--ee-app-border)] bg-white p-6">
        <flux:heading size="xs" level="3">{{ __('Statistics') }}</flux:heading>

        <div class="mt-4 grid gap-4 md:grid-cols-2">
            <flux:input wire:model="contact_type" :label="__('Primary contact type')" :disabled="$isLocked" />
            <flux:input wire:model="gospel_presentations" :label="__('Gospel presentations')" type="number" min="0" :disabled="$isLocked" />
            <flux:input wire:model="listeners_count" :label="__('Listeners')" type="number" min="0" :disabled="$isLocked" />
            <flux:input wire:model="results_decisions" :label="__('Decisions')" type="number" min="0" :disabled="$isLocked" />
            <flux:input wire:model="results_interested" :label="__('Interested')" type="number" min="0" :disabled="$isLocked" />
            <flux:input wire:model="results_rejection" :label="__('Rejection')" type="number" min="0" :disabled="$isLocked" />
            <flux:input wire:model="results_assurance" :label="__('Assurance')" type="number" min="0" :disabled="$isLocked" />
            <div class="flex items-center gap-3">
                <flux:switch wire:model="follow_up_scheduled" :disabled="$isLocked" />
                <span class="text-sm text-slate-700">{{ __('Follow-up scheduled') }}</span>
            </div>
        </div>

        <div class="mt-6 space-y-3">
            <div class="flex items-center justify-between">
                <div class="text-sm font-semibold text-heading">{{ __('Contact type counts') }}</div>
                <flux:button size="sm" type="button" variant="outline" wire:click="addContactTypeRow" :disabled="$isLocked">
                    {{ __('Add') }}
                </flux:button>
            </div>

            <div class="space-y-3">
                @foreach ($contactTypeCounts as $index => $row)
                    <div class="grid gap-3 md:grid-cols-[1fr,140px,auto]" wire:key="contact-type-row-{{ $index }}">
                        <flux:input wire:model="contactTypeCounts.{{ $index }}.type" :label="__('Contact type')" :disabled="$isLocked" />
                        <flux:input wire:model="contactTypeCounts.{{ $index }}.count" :label="__('Count')" type="number" min="0" :disabled="$isLocked" />
                        <div class="flex items-end">
                            <flux:button size="sm" type="button" variant="danger" wire:click="removeContactTypeRow({{ $index }})" :disabled="$isLocked">
                                {{ __('Remove') }}
                            </flux:button>
                        </div>
                    </div>
                    @error("contactTypeCounts.$index.type")
                        <div class="text-xs font-semibold text-red-600">{{ $message }}</div>
                    @enderror
                    @error("contactTypeCounts.$index.count")
                        <div class="text-xs font-semibold text-red-600">{{ $message }}</div>
                    @enderror
                @endforeach
            </div>
        </div>
    </section>

    <section class="rounded-2xl border border-[color:var(--ee-app-border)] bg-white p-6">
        <flux:heading size="xs" level="3">{{ __('Gospel Outline Participation') }}</flux:heading>

        <div class="mt-4 space-y-6">
            @foreach ($team->trainees as $trainee)
                @php
                    $traineeId = $trainee->trainee_id;
                @endphp
                <div class="rounded-xl border border-[color:var(--ee-app-border)] p-4" wire:key="outline-trainee-{{ $traineeId }}">
                    <div class="text-sm font-semibold text-heading">
                        {{ $trainee->trainee?->name ?? __('Trainee') }}
                    </div>

                    <div class="mt-4 space-y-4">
                        @foreach ($outlinePoints as $key => $label)
                            <div class="rounded-lg border border-[color:var(--ee-app-border)] p-4" wire:key="outline-point-{{ $traineeId }}-{{ $key }}">
                                <div class="flex items-center gap-3">
                                    <flux:checkbox wire:model="outline.{{ $traineeId }}.{{ $key }}.enabled" :disabled="$isLocked" />
                                    <div class="text-sm font-semibold text-heading">{{ $label }}</div>
                                </div>

                                @if (!empty($outline[$traineeId][$key]['enabled']))
                                    <div class="mt-4 grid gap-4 md:grid-cols-2">
                                        <flux:select wire:model="outline.{{ $traineeId }}.{{ $key }}.type" :label="__('Type')" :disabled="$isLocked">
                                            <option value="">{{ __('Select') }}</option>
                                            <option value="testimony">{{ __('Testimony') }}</option>
                                            <option value="illustration">{{ __('Illustration') }}</option>
                                        </flux:select>
                                        <flux:input wire:model="outline.{{ $traineeId }}.{{ $key }}.description" :label="__('Short description')" :disabled="$isLocked" />
                                    </div>
                                    @error("outline.$traineeId.$key.type")
                                        <div class="mt-2 text-xs font-semibold text-red-600">{{ $message }}</div>
                                    @enderror
                                    @error("outline.$traineeId.$key.description")
                                        <div class="mt-2 text-xs font-semibold text-red-600">{{ $message }}</div>
                                    @enderror
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
    </section>

    <section class="rounded-2xl border border-[color:var(--ee-app-border)] bg-white p-6">
        <flux:heading size="xs" level="3">{{ __('Public Report') }}</flux:heading>
        <div class="mt-4">
            <flux:textarea wire:model="lesson_learned" :label="__('Lesson learned')" rows="4" :disabled="$isLocked" />
            @error('lesson_learned')
                <div class="mt-2 text-xs font-semibold text-red-600">{{ $message }}</div>
            @enderror
        </div>
    </section>

    <div class="flex flex-wrap items-center gap-3">
        <flux:button variant="outline" type="submit" wire:loading.attr="disabled" :disabled="$isLocked">
            {{ __('Save draft') }}
        </flux:button>
        <flux:button variant="primary" type="button" wire:click="submitReport" wire:loading.attr="disabled" :disabled="$isLocked">
            {{ __('Submit report') }}
        </flux:button>
    </div>
</form>
