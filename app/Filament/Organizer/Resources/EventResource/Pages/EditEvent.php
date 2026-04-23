<?php

namespace App\Filament\Organizer\Resources\EventResource\Pages;

use App\Enums\EventStatus;
use App\Filament\Organizer\Resources\EventResource;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditEvent extends EditRecord
{
    protected static string $resource = EventResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('publish')
                ->label('Publish Event')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->visible(fn () => $this->record->status === EventStatus::Draft)
                ->requiresConfirmation()
                ->action(function () {
                    $this->record->update([
                        'status'       => EventStatus::Published,
                        'published_at' => now(),
                    ]);
                    Notification::make()->title('🎉 Event published!')->success()->send();
                }),

            Actions\Action::make('view')
                ->label('View Public Page')
                ->icon('heroicon-o-arrow-top-right-on-square')
                ->color('gray')
                ->url(fn () => route('events.show', $this->record))
                ->openUrlInNewTab(),

            Actions\DeleteAction::make(),
        ];
    }
}
