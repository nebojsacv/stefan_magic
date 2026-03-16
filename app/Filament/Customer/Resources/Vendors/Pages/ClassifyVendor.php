<?php

namespace App\Filament\Customer\Resources\Vendors\Pages;

use App\Filament\Customer\Resources\Vendors\VendorResource;
use App\Mail\QuestionnaireInvitation;
use App\Models\ClassificationQuestion;
use App\Models\Questionnaire;
use App\Models\QuestionnaireTemplate;
use App\Models\Vendor;
use App\Models\VendorClassification;
use App\Services\TierClassificationService;
use Filament\Actions\Action;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Livewire\Attributes\Computed;

class ClassifyVendor extends Page
{
    protected static string $resource = VendorResource::class;

    protected string $view = 'filament.customer.resources.vendors.pages.classify-vendor';

    public Vendor $record;

    public ?array $data = [];

    public bool $isPreCertified = false;

    public function mount(Vendor $record): void
    {
        $this->record = $record;
        $this->isPreCertified = $record->classification_method === 'manual' && $record->classification_status === 'approved';

        $this->data = [
            'risk_level' => $record->current_risk_level,
            'classification_method' => $record->classification_method ?? 'guided',
            'is_pre_certified' => $this->isPreCertified,
            'data_access_level' => null,
            'dependency_level' => null,
            'criticality_score' => null,
            'notes' => null,
            'q1' => null,
            'q2' => null,
            'q3' => null,
            'q4' => null,
            'q5' => null,
            'tier_manual_override' => null,
        ];
    }

    public function form(Schema $schema): Schema
    {
        $classificationQuestions = ClassificationQuestion::active()->get();

        return $schema
            ->statePath('data')
            ->components([
                Section::make('Classification Method')
                    ->description('Choose how to classify this vendor.')
                    ->schema([
                        Radio::make('classification_method')
                            ->label('Method')
                            ->options([
                                'guided' => 'Guided — Answer 5 questions to determine the risk tier automatically',
                                'manual' => 'Manual — Classify this vendor directly',
                            ])
                            ->default('guided')
                            ->live()
                            ->required(),

                        Toggle::make('is_pre_certified')
                            ->label('Pre-certified vendor')
                            ->helperText('Mark vendors like Google, AWS, or other certified providers that don\'t require full assessment.')
                            ->live()
                            ->visible(fn ($get) => $get('classification_method') === 'manual'),
                    ]),

                Section::make('Manual Classification')
                    ->description('Set the risk level and details for this vendor directly.')
                    ->visible(fn ($get) => $get('classification_method') === 'manual')
                    ->schema([
                        Radio::make('risk_level')
                            ->label('Risk Level')
                            ->options([
                                'low' => 'Low — Minimal risk, standard monitoring',
                                'medium' => 'Medium — Moderate risk, periodic review required',
                                'high' => 'High — Significant risk, frequent assessment required',
                            ])
                            ->required()
                            ->visible(fn ($get) => ! $get('is_pre_certified')),

                        Select::make('data_access_level')
                            ->label('Data Access Level')
                            ->options([
                                'none' => 'None',
                                'public' => 'Public',
                                'internal' => 'Internal',
                                'confidential' => 'Confidential',
                                'restricted' => 'Restricted',
                            ])
                            ->visible(fn ($get) => ! $get('is_pre_certified')),

                        Select::make('dependency_level')
                            ->label('Dependency Level')
                            ->options([
                                'none' => 'None',
                                'low' => 'Low',
                                'medium' => 'Medium',
                                'high' => 'High',
                                'critical' => 'Critical',
                            ])
                            ->visible(fn ($get) => ! $get('is_pre_certified')),

                        Textarea::make('notes')
                            ->label('Notes')
                            ->placeholder('Add any relevant notes about this classification…')
                            ->rows(3),
                    ]),

                Section::make('Guided Classification')
                    ->description('Answer these 5 questions about the vendor. The algorithm will compute the risk tier automatically.')
                    ->visible(fn ($get) => $get('classification_method') === 'guided')
                    ->schema(
                        $classificationQuestions->map(fn (ClassificationQuestion $q) => Radio::make($q->key)
                            ->label($q->label)
                            ->helperText($q->description)
                            ->options(['yes' => 'Yes', 'no' => 'No'])
                            ->required()
                            ->inline()
                        )->toArray()
                    ),

                Section::make('Manual Override (Optional)')
                    ->description('The algorithm computed a tier based on your answers. You may upgrade it, but NIS2 does not allow downgrading.')
                    ->visible(fn ($get) => $get('classification_method') === 'guided'
                        && $get('q1') !== null
                        && $get('q2') !== null
                        && $get('q3') !== null
                        && $get('q4') !== null
                        && $get('q5') !== null
                    )
                    ->schema([
                        Select::make('tier_manual_override')
                            ->label('Override Tier')
                            ->placeholder('No override — keep algorithm result')
                            ->options(function ($get): array {
                                $service = new TierClassificationService;
                                $answers = [
                                    'q1' => $get('q1') ?? 'no',
                                    'q2' => $get('q2') ?? 'no',
                                    'q3' => $get('q3') ?? 'no',
                                    'q4' => $get('q4') ?? 'no',
                                    'q5' => $get('q5') ?? 'no',
                                ];
                                $systemTier = $service->computeTier($answers);

                                return $service->allowedOverrides($systemTier);
                            })
                            ->live()
                            ->helperText(function ($get): string {
                                $service = new TierClassificationService;
                                $answers = [
                                    'q1' => $get('q1') ?? 'no',
                                    'q2' => $get('q2') ?? 'no',
                                    'q3' => $get('q3') ?? 'no',
                                    'q4' => $get('q4') ?? 'no',
                                    'q5' => $get('q5') ?? 'no',
                                ];
                                $systemTier = $service->computeTier($answers);
                                $finalTier = $service->resolveFinalTier($systemTier, $get('tier_manual_override'));

                                return 'Algorithm result: '.strtoupper($systemTier)
                                    .' → Final tier: '.strtoupper($finalTier);
                            }),
                    ]),
            ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('save')
                ->label('Save Classification')
                ->icon(Heroicon::OutlinedCheckCircle)
                ->color('primary')
                ->action('saveClassification'),

            Action::make('back')
                ->label('Back to Vendors')
                ->icon(Heroicon::OutlinedArrowLeft)
                ->color('gray')
                ->url(VendorResource::getUrl('index')),
        ];
    }

    public function saveClassification(): void
    {
        $data = $this->data;
        $method = $data['classification_method'];

        if ($method === 'guided') {
            $this->saveGuidedClassification($data);

            return;
        }

        $this->saveManualClassification($data);
    }

    protected function saveGuidedClassification(array $data): void
    {
        $answers = [
            'q1' => $data['q1'] ?? 'no',
            'q2' => $data['q2'] ?? 'no',
            'q3' => $data['q3'] ?? 'no',
            'q4' => $data['q4'] ?? 'no',
            'q5' => $data['q5'] ?? 'no',
        ];

        $service = new TierClassificationService;
        $tierSystem = $service->computeTier($answers);
        $tierOverride = $data['tier_manual_override'] ?: null;
        $tierFinal = $service->resolveFinalTier($tierSystem, $tierOverride);

        $template = QuestionnaireTemplate::where('is_active', true)
            ->where('risk_level', $tierFinal)
            ->first();

        if (! $template) {
            Notification::make()
                ->title('No questionnaire template available')
                ->body("Please contact administrator to set up a {$tierFinal}-tier questionnaire template.")
                ->danger()
                ->send();

            return;
        }

        VendorClassification::create([
            'vendor_id' => $this->record->id,
            'risk_level' => $tierFinal,
            'tier_system' => $tierSystem,
            'tier_manual_override' => $tierOverride,
            'tier_final' => $tierFinal,
            'classification_method' => 'guided',
            'classification_answers' => $answers,
            'classified_by' => Auth::id(),
        ]);

        $questionnaire = Questionnaire::create([
            'vendor_id' => $this->record->id,
            'template_id' => $template->id,
            'user_id' => Auth::id(),
            'status' => 'sent',
        ]);

        $this->record->update([
            'current_risk_level' => $tierFinal,
            'classification_method' => 'guided',
            'classification_status' => 'pending_approval',
        ]);

        $questionnaireUrl = url('/q/'.$questionnaire->unique_id);
        Mail::to($this->record->poc_email)->send(new QuestionnaireInvitation($questionnaire, $questionnaireUrl));

        Notification::make()
            ->title('Vendor classified — questionnaire sent')
            ->body(
                'Tier: '.strtoupper($tierFinal).
                ($tierOverride ? ' (algorithm: '.strtoupper($tierSystem).', overridden)' : '').
                '. A '.$template->question_count."-question assessment has been sent to {$this->record->poc_email}."
            )
            ->success()
            ->send();

        $this->redirect(VendorResource::getUrl('index'));
    }

    protected function saveManualClassification(array $data): void
    {
        $isPreCertified = (bool) ($data['is_pre_certified'] ?? false);

        VendorClassification::create([
            'vendor_id' => $this->record->id,
            'risk_level' => $isPreCertified ? 'low' : $data['risk_level'],
            'classification_method' => 'manual',
            'data_access_level' => $isPreCertified ? 'none' : ($data['data_access_level'] ?? null),
            'dependency_level' => $isPreCertified ? 'none' : ($data['dependency_level'] ?? null),
            'notes' => $data['notes'] ?? ($isPreCertified ? 'Pre-certified vendor — manual override.' : null),
            'classified_by' => Auth::id(),
        ]);

        $this->record->update([
            'current_risk_level' => $isPreCertified ? 'low' : $data['risk_level'],
            'classification_method' => 'manual',
            'classification_status' => 'approved',
            'approved_by' => Auth::id(),
            'approved_at' => now(),
        ]);

        Notification::make()
            ->title($isPreCertified ? 'Vendor marked as pre-certified' : 'Vendor classified successfully')
            ->body($isPreCertified
                ? "{$this->record->name} has been marked as a pre-certified vendor with low risk."
                : "{$this->record->name} has been classified as ".ucfirst($data['risk_level']).' risk.')
            ->success()
            ->send();

        $this->redirect(VendorResource::getUrl('index'));
    }

    #[Computed]
    public function vendorName(): string
    {
        return $this->record->name;
    }

    #[Computed]
    public function pocEmail(): string
    {
        return $this->record->poc_email;
    }
}
