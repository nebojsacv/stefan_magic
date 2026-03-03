<?php

namespace App\Filament\Customer\Resources\Vendors\Pages;

use App\Filament\Customer\Resources\Vendors\VendorResource;
use App\Mail\QuestionnaireInvitation;
use App\Models\Questionnaire;
use App\Models\QuestionnaireTemplate;
use App\Models\Vendor;
use App\Models\VendorClassification;
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
        ];
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->statePath('data')
            ->components([
                Section::make('Classification Method')
                    ->description('Choose how to classify this vendor.')
                    ->schema([
                        Radio::make('classification_method')
                            ->label('Method')
                            ->options([
                                'guided' => 'Guided — Use a questionnaire to determine risk level',
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
                    ->description('A questionnaire will be sent to the vendor\'s point of contact. Once submitted, AI will analyse the answers and suggest a risk level for you to review.')
                    ->visible(fn ($get) => $get('classification_method') === 'guided')
                    ->schema([]),
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
            $this->redirectToGuidedFlow();

            return;
        }

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

    protected function redirectToGuidedFlow(): void
    {
        $template = QuestionnaireTemplate::where('is_active', true)
            ->where('risk_level', 'medium')
            ->first();

        if (! $template) {
            $template = QuestionnaireTemplate::where('is_active', true)->first();
        }

        if (! $template) {
            Notification::make()
                ->title('No questionnaire template available')
                ->body('Please contact administrator to set up questionnaire templates.')
                ->danger()
                ->send();

            return;
        }

        $questionnaire = Questionnaire::create([
            'vendor_id' => $this->record->id,
            'template_id' => $template->id,
            'user_id' => Auth::id(),
            'status' => 'sent',
        ]);

        $this->record->update([
            'classification_method' => 'guided',
            'classification_status' => 'pending_approval',
        ]);

        $questionnaireUrl = url('/q/'.$questionnaire->unique_id);
        Mail::to($this->record->poc_email)->send(new QuestionnaireInvitation($questionnaire, $questionnaireUrl));

        Notification::make()
            ->title('Questionnaire sent')
            ->body("A security assessment questionnaire has been sent to {$this->record->poc_email}. You will be notified when it is completed.")
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
