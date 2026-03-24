<?php

namespace App\Livewire;

use App\Models\Questionnaire;
use App\Models\QuestionnaireAnswer;
use App\Services\AiAnalysisService;
use Livewire\Component;
use Livewire\WithFileUploads;

class PublicQuestionnaire extends Component
{
    use WithFileUploads;

    public Questionnaire $questionnaire;

    public array $answers = [];

    /** @var array<int, \Livewire\Features\SupportFileUploads\TemporaryUploadedFile|null> */
    public array $evidenceFiles = [];

    public function mount(string $uniqueId): void
    {
        $this->questionnaire = Questionnaire::query()
            ->where('unique_id', $uniqueId)
            ->with(['template.questions.options', 'vendor'])
            ->firstOrFail();

        if ($this->questionnaire->is_submitted) {
            $this->redirect(route('questionnaire.thank-you'));

            return;
        }

        $this->questionnaire->update([
            'is_opened' => true,
            'status' => 'in_progress',
        ]);

        foreach ($this->questionnaire->answers as $answer) {
            $question = $this->questionnaire->template->questions->firstWhere('id', $answer->question_id);
            $this->answers[$answer->question_id] = $this->formatAnswerForInput($answer, $question);
        }
    }

    public function submit(): void
    {
        $this->validate($this->buildValidationRules());

        $questions = $this->questionnaire->template->questions;

        foreach ($questions as $question) {
            $value = $this->answers[$question->id] ?? null;

            if ($value === null || $value === '') {
                continue;
            }

            QuestionnaireAnswer::updateOrCreate(
                [
                    'questionnaire_id' => $this->questionnaire->id,
                    'question_id' => $question->id,
                ],
                $this->formatAnswerForStorage($question, $value)
            );
        }

        // Store evidence files and attach paths to the relevant answers
        foreach ($this->evidenceFiles as $questionId => $file) {
            if (! $file) {
                continue;
            }

            $path = $file->store(
                "questionnaire-evidence/{$this->questionnaire->id}",
                'local'
            );

            $answer = QuestionnaireAnswer::firstOrCreate(
                [
                    'questionnaire_id' => $this->questionnaire->id,
                    'question_id' => $questionId,
                ],
                [
                    'questionnaire_id' => $this->questionnaire->id,
                    'question_id' => $questionId,
                ]
            );

            $answer->update([
                'evidence_files' => [
                    [
                        'filename' => $file->getClientOriginalName(),
                        'path' => $path,
                        'mime_type' => $file->getMimeType(),
                        'size' => $file->getSize(),
                    ],
                ],
            ]);
        }

        $this->questionnaire->update([
            'is_submitted' => true,
            'status' => 'submitted',
            'submitted_at' => now(),
            'questions_completed' => $questions->count(),
        ]);

        app(AiAnalysisService::class)->analyze($this->questionnaire->fresh());

        $this->redirect(route('questionnaire.thank-you'));
    }

    /**
     * Build dynamic validation rules depending on each question's evidence_required_when setting.
     *
     * @return array<string, string>
     */
    protected function buildValidationRules(): array
    {
        $fileMimes = 'file|mimes:pdf,jpg,jpeg,png,gif,webp|max:10240';
        $rules = [];

        foreach ($this->questionnaire->template->questions as $question) {
            $key = "evidenceFiles.{$question->id}";

            if (! $question->hasEvidenceUpload()) {
                continue;
            }

            if ($question->isEvidenceRequired($this->answers[$question->id] ?? null)) {
                $rules[$key] = "required|{$fileMimes}";
            } else {
                $rules[$key] = "nullable|{$fileMimes}";
            }
        }

        return $rules;
    }

    protected function formatAnswerForInput(QuestionnaireAnswer $answer, ?\App\Models\Question $question): mixed
    {
        if ($answer->selected_options !== null && is_array($answer->selected_options)) {
            return ($question && $question->type === 'checkbox') ? $answer->selected_options : ($answer->selected_options[0] ?? null);
        }

        return $answer->answer_text ?? '';
    }

    protected function formatAnswerForStorage(\App\Models\Question $question, mixed $value): array
    {
        $data = [
            'questionnaire_id' => $this->questionnaire->id,
            'question_id' => $question->id,
        ];

        if (in_array($question->type, ['select_bool', 'select_other', 'radio', 'checkbox'])) {
            $data['selected_options'] = is_array($value) ? array_values($value) : [$value];
        } else {
            $data['answer_text'] = is_array($value) ? implode("\n", $value) : (string) $value;
        }

        return $data;
    }

    public function render()
    {
        return view('livewire.public-questionnaire')->layout('layouts.guest');
    }
}
