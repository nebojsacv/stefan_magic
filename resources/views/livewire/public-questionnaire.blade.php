<div class="w-full max-w-2xl">
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
                        @if ($question->need_evidence)
                            <span class="text-amber-600">(Evidence may be required)</span>
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
                </div>
            @endforeach

            <div class="pt-6">
                <button type="submit"
                    class="w-full sm:w-auto px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                    Submit Questionnaire
                </button>
            </div>
        </form>
    </div>
</div>
