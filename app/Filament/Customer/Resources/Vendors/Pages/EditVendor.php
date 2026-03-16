<?php

namespace App\Filament\Customer\Resources\Vendors\Pages;

use App\Filament\Customer\Resources\Questionnaires\QuestionnaireResource;
use App\Filament\Customer\Resources\Vendors\VendorResource;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Icons\Heroicon;

class EditVendor extends EditRecord
{
    protected static string $resource = VendorResource::class;

    protected function getHeaderActions(): array
    {
        $questionnaire = $this->record->questionnaires()
            ->whereIn('status', ['sent', 'in_progress', 'opened'])
            ->where('is_submitted', false)
            ->latest()
            ->first();

        $questionnaireLink = $questionnaire ? url('/q/'.$questionnaire->unique_id) : null;

        $isClassified = ! is_null($this->record->classification_status);
        $isPendingApproval = $this->record->classification_status === 'pending_approval';

        $pendingQuestionnaire = $isPendingApproval
            ? $this->record->questionnaires()->where('is_submitted', true)->latest()->first()
            : null;

        $actions = [
            Action::make('review')
                ->label('Review AI Result')
                ->icon(Heroicon::OutlinedMagnifyingGlass)
                ->color('primary')
                ->visible($isPendingApproval && $pendingQuestionnaire !== null)
                ->url($pendingQuestionnaire
                    ? QuestionnaireResource::getUrl('review', ['record' => $pendingQuestionnaire])
                    : '#'
                ),

            Action::make('classify')
                ->label($isClassified ? 'Re-classify Vendor' : 'Classify Vendor')
                ->icon($isClassified ? Heroicon::OutlinedArrowPath : Heroicon::OutlinedShieldCheck)
                ->color($isClassified ? 'gray' : 'primary')
                ->url(VendorResource::getUrl('classify', ['record' => $this->record])),

            Action::make('questionnaireLink')
                ->label('Questionnaire Link')
                ->icon(Heroicon::OutlinedLink)
                ->color('info')
                ->visible((bool) $questionnaireLink)
                ->modalHeading('Questionnaire Link (for testing)')
                ->modalDescription('Copy this link to share with the vendor. It is also sent by email.')
                ->form([
                    TextInput::make('link')
                        ->label('Link')
                        ->default($questionnaireLink)
                        ->readOnly()
                        ->copyable(copyMessage: 'Link copied'),
                ])
                ->modalSubmitAction(false)
                ->modalCancelActionLabel('Close'),
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];

        return $actions;
    }
}
