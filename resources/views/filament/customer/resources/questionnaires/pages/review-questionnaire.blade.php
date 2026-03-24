<x-filament-panels::page>
    {{-- Vendor + AI Summary Header --}}
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">

        {{-- Vendor Info --}}
        <x-filament::section>
            <div style="display: flex; align-items: center; gap: 16px;">
                <div style="width: 52px; height: 52px; border-radius: 50%; background: #3b82f6; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                    <span style="color: white; font-size: 20px; font-weight: 700;">
                        {{ strtoupper(substr($this->record->vendor->name, 0, 1)) }}
                    </span>
                </div>
                <div>
                    <p style="font-size: 18px; font-weight: 700; margin: 0;">{{ $this->record->vendor->name }}</p>
                    <p style="font-size: 13px; color: #6b7280; margin: 4px 0 0;">{{ $this->record->vendor->poc_email }}</p>
                    @if($this->record->vendor->industry)
                        <p style="font-size: 13px; color: #6b7280; margin: 2px 0 0;">{{ $this->record->vendor->industry }}</p>
                    @endif
                </div>
            </div>

            <div style="margin-top: 16px; display: flex; gap: 8px; flex-wrap: wrap;">
                <x-filament::badge color="gray">
                    {{ $this->record->template->name ?? 'Unknown template' }}
                </x-filament::badge>
                <x-filament::badge color="gray">
                    {{ $this->record->questions_completed }} / {{ $this->record->template->question_count }} questions
                </x-filament::badge>
                @if($this->record->submitted_at)
                    <x-filament::badge color="info">
                        Submitted {{ $this->record->submitted_at->diffForHumans() }}
                    </x-filament::badge>
                @endif
            </div>
        </x-filament::section>

        {{-- AI Analysis Summary --}}
        @if($this->record->aiAnalysis)
            @php $ai = $this->record->aiAnalysis; @endphp
            <x-filament::section>
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px;">
                    <p style="font-weight: 600; font-size: 15px; margin: 0;">AI Analysis Result</p>
                    <span style="font-size: 11px; color: #9ca3af;">{{ $ai->model_used }} &bull; {{ round((float)$ai->confidence_score * 100) }}% confidence</span>
                </div>

                {{-- Risk Level + Score --}}
                <div style="display: flex; align-items: center; gap: 20px;">
                    <div style="text-align: center;">
                        <div style="width: 72px; height: 72px; border-radius: 50%; border: 4px solid {{ $this->riskColor }}; display: flex; align-items: center; justify-content: center;">
                            <span style="font-size: 22px; font-weight: 800; color: {{ $this->riskColor }};">{{ $this->scorePercent }}</span>
                        </div>
                        <p style="font-size: 11px; color: #6b7280; margin: 6px 0 0;">Risk Score</p>
                    </div>
                    <div>
                        <p style="font-size: 26px; font-weight: 800; color: {{ $this->riskColor }}; margin: 0; text-transform: uppercase;">
                            {{ $ai->risk_level }}
                        </p>
                        <p style="font-size: 13px; color: #6b7280; margin: 4px 0 0;">Risk Level</p>
                        <p style="font-size: 13px; color: #374151; margin: 8px 0 0;">{{ $ai->analysis_summary }}</p>
                    </div>
                </div>

                @if(!empty($ai->key_findings))
                    <div style="margin-top: 12px; padding-top: 12px; border-top: 1px solid #e5e7eb;">
                        <p style="font-size: 13px; font-weight: 600; margin: 0 0 6px;">Key Findings</p>
                        <ul style="margin: 0; padding-left: 18px; font-size: 13px; color: #374151;">
                            @foreach($ai->key_findings as $finding)
                                <li>{{ $finding }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            </x-filament::section>
        @else
            <x-filament::section>
                <div style="text-align: center; padding: 24px; color: #9ca3af;">
                    <p style="font-size: 14px;">AI analysis not yet available.</p>
                </div>
            </x-filament::section>
        @endif
    </div>

    {{-- Vendor Answers by Section --}}
    @foreach($this->groupedAnswers as $section => $answers)
        <x-filament::section :heading="$section">
            <div style="display: flex; flex-direction: column; gap: 0;">
                @foreach($answers as $index => $answer)
                    @php
                        $question = $answer->question;
                        $responseText = is_array($answer->selected_options) ? implode(', ', $answer->selected_options) : ($answer->answer_text ?? '—');
                        $isYes = strtolower(trim($responseText)) === 'yes';
                        $isNo = strtolower(trim($responseText)) === 'no';
                        $isNa = strtolower(trim($responseText)) === 'n/a';
                        $responseColor = $isYes ? '#16a34a' : ($isNo ? '#dc2626' : '#6b7280');
                        $rowBg = $index % 2 === 0 ? '#f9fafb' : '#ffffff';
                    @endphp
                    <div style="padding: 12px 16px; background: {{ $rowBg }}; border-radius: 6px;">
                        <div style="display: flex; align-items: flex-start; justify-content: space-between; gap: 16px;">
                            <p style="font-size: 13px; font-weight: 500; color: #111827; margin: 0; flex: 1;">
                                {{ $loop->iteration }}. {{ $question->question_text }}
                            </p>
                            <div style="flex-shrink: 0; text-align: right; min-width: 80px;">
                                <span style="font-size: 13px; font-weight: 700; color: {{ $responseColor }};">
                                    {{ $responseText }}
                                </span>
                                @if($question->scoring_weight)
                                    <p style="font-size: 11px; color: #9ca3af; margin: 2px 0 0;">weight {{ $question->scoring_weight }}</p>
                                @endif
                            </div>
                        </div>

                        @if(!empty($answer->evidence_files))
                            <div style="margin-top: 10px; display: flex; flex-wrap: wrap; gap: 8px;">
                                @foreach($answer->evidence_files as $file)
                                    @php
                                        $fileUrl = route('evidence.serve', ltrim(str_replace('questionnaire-evidence/', '', $file['path']), '/'));
                                        $isImage = str_starts_with($file['mime_type'] ?? '', 'image/');
                                        $filename = $file['filename'] ?? basename($file['path']);
                                        $sizeKb = isset($file['size']) ? round($file['size'] / 1024, 1) : null;
                                    @endphp

                                    @if($isImage)
                                        <a href="{{ $fileUrl }}" target="_blank"
                                           style="display: inline-block; border: 1px solid #e5e7eb; border-radius: 8px; overflow: hidden; text-decoration: none;">
                                            <img src="{{ $fileUrl }}"
                                                 alt="{{ $filename }}"
                                                 style="display: block; max-height: 120px; max-width: 200px; object-fit: cover;">
                                            <div style="padding: 4px 8px; background: #f9fafb; font-size: 11px; color: #6b7280;">
                                                {{ $filename }}
                                                @if($sizeKb)
                                                    &bull; {{ $sizeKb }} KB
                                                @endif
                                            </div>
                                        </a>
                                    @else
                                        <a href="{{ $fileUrl }}" target="_blank"
                                           style="display: inline-flex; align-items: center; gap: 8px; padding: 8px 12px; background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 8px; text-decoration: none; color: #374151; font-size: 13px;">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#ef4444" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/>
                                            </svg>
                                            <span>
                                                {{ $filename }}
                                                @if($sizeKb)
                                                    <span style="color: #9ca3af; font-size: 11px; margin-left: 4px;">{{ $sizeKb }} KB</span>
                                                @endif
                                            </span>
                                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#9ca3af" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/>
                                            </svg>
                                        </a>
                                    @endif
                                @endforeach
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        </x-filament::section>
    @endforeach

    <x-filament-actions::modals />
</x-filament-panels::page>
