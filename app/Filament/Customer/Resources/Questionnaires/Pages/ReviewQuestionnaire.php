<?php

namespace App\Filament\Customer\Resources\Questionnaires\Pages;

use App\Filament\Customer\Resources\Questionnaires\QuestionnaireResource;
use App\Filament\Customer\Resources\Vendors\VendorResource;
use App\Models\Questionnaire;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;

class ReviewQuestionnaire extends Page
{
    protected static string $resource = QuestionnaireResource::class;

    protected string $view = 'filament.customer.resources.questionnaires.pages.review-questionnaire';

    public Questionnaire $record;

    public function mount(Questionnaire $record): void
    {
        $this->record = $record->load([
            'vendor',
            'template',
            'answers.question.options',
            'aiAnalysis',
        ]);
    }

    protected function getHeaderActions(): array
    {
        $isAlreadyApproved = $this->record->vendor->classification_status === 'approved';

        return [
            Action::make('approve')
                ->label($isAlreadyApproved ? 'Re-approve' : 'Approve Classification')
                ->icon(Heroicon::OutlinedCheckCircle)
                ->color('success')
                ->requiresConfirmation()
                ->modalHeading('Approve Classification')
                ->modalDescription(function (): string {
                    $aiRisk = $this->record->aiAnalysis?->risk_level ?? 'unknown';

                    return 'This will approve the AI-computed risk level of "'.strtoupper($aiRisk)."\" for {$this->record->vendor->name}. The vendor classification will be marked as approved.";
                })
                ->action(fn () => $this->approveClassification()),

            Action::make('override')
                ->label('Override Risk Level')
                ->icon(Heroicon::OutlinedPencilSquare)
                ->color('warning')
                ->form([
                    Select::make('risk_level')
                        ->label('Set Risk Level')
                        ->options([
                            'low' => 'Low',
                            'medium' => 'Medium',
                            'high' => 'High',
                        ])
                        ->default($this->record->aiAnalysis?->risk_level)
                        ->required()
                        ->helperText('Override the AI result if you believe the risk level should be different.'),
                ])
                ->action(fn (array $data) => $this->overrideAndApprove($data['risk_level'])),

            Action::make('back')
                ->label('Back to Vendors')
                ->icon(Heroicon::OutlinedArrowLeft)
                ->color('gray')
                ->url(VendorResource::getUrl('index')),
        ];
    }

    protected function approveClassification(): void
    {
        $this->record->vendor->update([
            'classification_status' => 'approved',
            'approved_by' => Auth::id(),
            'approved_at' => now(),
        ]);

        Notification::make()
            ->title('Classification approved')
            ->body("{$this->record->vendor->name} has been approved with ".strtoupper($this->record->aiAnalysis?->risk_level ?? 'unknown').' risk.')
            ->success()
            ->send();

        $this->redirect(VendorResource::getUrl('index'));
    }

    protected function overrideAndApprove(string $riskLevel): void
    {
        $this->record->vendor->update([
            'current_risk_level' => $riskLevel,
            'classification_status' => 'approved',
            'approved_by' => Auth::id(),
            'approved_at' => now(),
        ]);

        Notification::make()
            ->title('Classification approved with override')
            ->body("{$this->record->vendor->name} has been approved with ".strtoupper($riskLevel).' risk (overridden from AI result).')
            ->success()
            ->send();

        $this->redirect(VendorResource::getUrl('index'));
    }

    #[Computed]
    public function riskColor(): string
    {
        return match ($this->record->aiAnalysis?->risk_level) {
            'high' => '#ef4444',
            'medium' => '#f59e0b',
            'low' => '#22c55e',
            default => '#6b7280',
        };
    }

    #[Computed]
    public function scorePercent(): int
    {
        return min(100, (int) ($this->record->aiAnalysis?->total_risk_score ?? 0));
    }

    #[Computed]
    public function groupedAnswers(): array
    {
        $grouped = [];

        foreach ($this->record->answers as $answer) {
            $section = $answer->question->question_category ?? 'General';
            $grouped[$section][] = $answer;
        }

        return $grouped;
    }
}
