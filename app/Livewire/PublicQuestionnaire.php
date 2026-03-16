<?php

namespace App\Livewire;

use App\Models\Questionnaire;
use App\Models\QuestionnaireAnswer;
use App\Services\AiAnalysisService;
use Livewire\Component;

class PublicQuestionnaire extends Component
{
    public Questionnaire $questionnaire;

    public array $answers = [];

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

        $this->questionnaire->update([
            'is_submitted' => true,
            'status' => 'submitted',
            'submitted_at' => now(),
            'questions_completed' => $questions->count(),
        ]);

        app(AiAnalysisService::class)->analyze($this->questionnaire->fresh());

        $this->redirect(route('questionnaire.thank-you'));
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
