<?php

namespace App\Filament\Customer\Widgets;

use App\Filament\Customer\Resources\Questionnaires\QuestionnaireResource;
use App\Models\Questionnaire;
use Filament\Actions\Action;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Support\Facades\Auth;

class RecentQuestionnaires extends BaseWidget
{
    protected static ?int $sort = 3;

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Questionnaire::query()
                    ->where('user_id', Auth::id())
                    ->with(['vendor', 'template'])
                    ->latest()
                    ->limit(5)
            )
            ->heading('Recent Questionnaires')
            ->description('Your latest vendor questionnaires')
            ->columns([
                TextColumn::make('vendor.name')
                    ->label('Vendor')
                    ->weight('semibold')
                    ->searchable(),

                TextColumn::make('template.name')
                    ->label('Template')
                    ->placeholder('—')
                    ->color('gray'),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'completed' => 'success',
                        'submitted', 'processing' => 'primary',
                        'in_progress', 'opened' => 'warning',
                        'sent' => 'info',
                        'failed' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => ucwords(str_replace('_', ' ', $state))),

                IconColumn::make('is_submitted')
                    ->label('Submitted')
                    ->boolean()
                    ->alignCenter(),

                TextColumn::make('questions_completed')
                    ->label('Progress')
                    ->formatStateUsing(fn (Questionnaire $record): string => $record->questions_completed.' questions completed')
                    ->color('gray'),

                TextColumn::make('submitted_at')
                    ->label('Submitted At')
                    ->dateTime('M j, Y')
                    ->placeholder('—')
                    ->color('gray'),

                TextColumn::make('created_at')
                    ->label('Sent')
                    ->since()
                    ->color('gray'),
            ])
            ->actions([
                Action::make('view')
                    ->label('View')
                    ->icon('heroicon-m-eye')
                    ->url(fn (Questionnaire $record): string => QuestionnaireResource::getUrl('edit', ['record' => $record])),
            ])
            ->emptyStateHeading('No questionnaires yet')
            ->emptyStateDescription('Send a questionnaire to a vendor to get started.')
            ->emptyStateIcon('heroicon-o-document-text')
            ->paginated(false);
    }
}
