<div class="w-full max-w-2xl">
    {{-- AI processing overlay --}}
    <div wire:loading.flex wire:target="submit"
         style="position:fixed; inset:0; z-index:9999; align-items:center; justify-content:center; background:rgba(0,0,0,0.6);">
        <div style="background:#fff; border-radius:16px; box-shadow:0 20px 60px rgba(0,0,0,0.3); padding:40px 48px; display:flex; flex-direction:column; align-items:center; gap:16px; max-width:360px; width:90%; text-align:center;">
            <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none"
                 style="animation:spin 1s linear infinite; color:#2563eb; flex-shrink:0;">
                <style>@keyframes spin { to { transform: rotate(360deg); } }</style>
                <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" style="opacity:.25;"></circle>
                <path fill="currentColor" style="opacity:.85;" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
            </svg>
            <p style="font-size:18px; font-weight:700; color:#111827; margin:0;">Analysing your responses…</p>
            <p style="font-size:14px; color:#6b7280; margin:0; line-height:1.5;">Our AI is reviewing your answers.<br>This usually takes 20–40 seconds.<br><strong>Please do not close this page.</strong></p>
        </div>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6 sm:p-8">
        <h1 class="text-2xl font-semibold mb-2">Security Assessment — {{ $questionnaire->vendor->name }}</h1>
        <p class="text-gray-600 dark:text-gray-400 mb-6">
            Please complete the following questionnaire. This should take approximately 20–30 minutes.
        </p>

        <form wire:submit="submit" class="space-y-8">
            @foreach ($questionnaire->template->questions as $question)
                <div class="border-b border-gray-200 dark:border-gray-700 pb-6 last:border-0">
                    <label class="block text-sm font-medium mb-3">
                        {{ $loop->iteration }}. {{ $question->question_text }}
                        @if ($question->evidence_required_when === 'always')
                            <span class="text-red-600 text-xs font-normal">(Evidence required)</span>
                        @elseif ($question->evidence_required_when === 'if_yes')
                            <span class="text-amber-600 text-xs font-normal">(Evidence required if Yes)</span>
                        @elseif ($question->evidence_required_when === 'optional')
                            <span class="text-blue-500 text-xs font-normal">(Evidence optional)</span>
                        @endif
                    </label>

                    @switch($question->type)
                        @case('select_bool')
                        @case('select_other')
                        @case('radio')
                            <div class="space-y-2">
                                @foreach ($question->options as $option)
                                    <label class="flex items-center gap-2 cursor-pointer">
                                        <input type="radio"
                                            wire:model.live="answers.{{ $question->id }}"
                                            value="{{ $option->option_text }}"
                                            name="answers[{{ $question->id }}]"
                                            class="rounded border-gray-300 dark:border-gray-600">
                                        <span>{{ $option->option_text }}</span>
                                    </label>
                                @endforeach
                            </div>
                            @break
                        @case('checkbox')
                            <div class="space-y-2">
                                @foreach ($question->options as $option)
                                    <label class="flex items-center gap-2 cursor-pointer">
                                        <input type="checkbox"
                                            wire:model.live="answers.{{ $question->id }}"
                                            value="{{ $option->option_text }}"
                                            class="rounded border-gray-300 dark:border-gray-600">
                                        <span>{{ $option->option_text }}</span>
                                    </label>
                                @endforeach
                            </div>
                            @break
                        @case('textarea')
                            <textarea wire:model.live="answers.{{ $question->id }}"
                                name="answers[{{ $question->id }}]"
                                rows="4"
                                class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:border-blue-500 focus:ring-blue-500"></textarea>
                            @break
                        @default
                            <input type="text"
                                wire:model.live="answers.{{ $question->id }}"
                                name="answers[{{ $question->id }}]"
                                class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    @endswitch

                    @if ($question->hasEvidenceUpload())
                        @php
                            $currentAnswer = $answers[$question->id] ?? null;
                            $showUpload = $question->evidence_required_when !== 'if_yes'
                                || $question->isEvidenceRequired($currentAnswer);
                            $isRequired = $question->isEvidenceRequired($currentAnswer);
                        @endphp

                        @if ($showUpload)
                            <div class="mt-3">
                                <div style="display:flex; align-items:center; gap:6px; margin-bottom:4px;">
                                    <label style="font-size:12px; font-weight:600; color:#6b7280;">
                                        Supporting evidence
                                    </label>
                                    @if ($isRequired)
                                        <span style="font-size:11px; font-weight:700; color:#dc2626; background:#fef2f2; padding:1px 6px; border-radius:9999px;">Required</span>
                                    @else
                                        <span style="font-size:11px; color:#9ca3af; background:#f3f4f6; padding:1px 6px; border-radius:9999px;">Optional</span>
                                    @endif
                                    <span style="font-size:11px; color:#9ca3af;">(PDF, JPG, PNG — max 10 MB)</span>
                                </div>

                                <label
                                    style="display:flex; align-items:center; gap:10px; padding:10px 14px; border:2px dashed {{ $isRequired ? '#fca5a5' : '#d1d5db' }}; border-radius:8px; cursor:pointer; background:{{ $isRequired ? '#fff7f7' : '#f9fafb' }}; transition:border-color 0.2s;"
                                    onmouseover="this.style.borderColor='#3b82f6'"
                                    onmouseout="this.style.borderColor='{{ $isRequired ? '#fca5a5' : '#d1d5db' }}'">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#6b7280" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0;">
                                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/>
                                    </svg>
                                    <span style="font-size:13px; color:#6b7280;">
                                        @if (!empty($evidenceFiles[$question->id]))
                                            <span style="color:#059669; font-weight:600;">
                                                &#10003; {{ $evidenceFiles[$question->id]->getClientOriginalName() }}
                                            </span>
                                            <span style="color:#9ca3af; margin-left:6px;">(click to change)</span>
                                        @else
                                            Click to upload file
                                        @endif
                                    </span>
                                    <input type="file"
                                        wire:model="evidenceFiles.{{ $question->id }}"
                                        accept=".pdf,.jpg,.jpeg,.png,.gif,.webp"
                                        style="position:absolute; width:1px; height:1px; opacity:0; overflow:hidden;">
                                </label>

                                <div wire:loading wire:target="evidenceFiles.{{ $question->id }}"
                                     style="font-size:12px; color:#3b82f6; margin-top:4px;">
                                    Uploading…
                                </div>

                                @error('evidenceFiles.' . $question->id)
                                    <p style="font-size:12px; color:#dc2626; margin-top:4px;">{{ $message }}</p>
                                @enderror
                            </div>
                        @elseif ($question->evidence_required_when === 'if_yes')
                            {{-- Hint shown when vendor has not yet answered Yes --}}
                            <p style="font-size:12px; color:#d97706; margin-top:6px;">
                                &#9432; If you answer <strong>Yes</strong>, you will be required to attach supporting evidence.
                            </p>
                        @endif
                    @endif
                </div>
            @endforeach

            <div class="pt-6">
                <button type="submit"
                    wire:loading.attr="disabled"
                    wire:target="submit"
                    class="w-full sm:w-auto px-6 py-3 bg-blue-600 hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed text-white font-medium rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 flex items-center gap-2">
                    <svg wire:loading wire:target="submit"
                         xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
                         style="animation:spin 1s linear infinite; flex-shrink:0;">
                        <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" style="opacity:.25;"></circle>
                        <path fill="currentColor" style="opacity:.85;" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                    </svg>
                    <span wire:loading.remove wire:target="submit">Submit Questionnaire</span>
                    <span wire:loading wire:target="submit">Analysing…</span>
                </button>
            </div>
        </form>
    </div>
</div>
