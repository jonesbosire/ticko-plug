<?php

namespace App\Filament\Organizer\Resources\EventResource\Pages;

use App\Filament\Organizer\Resources\EventResource;
use Filament\Resources\Pages\CreateRecord;

class CreateEvent extends CreateRecord
{
    protected static string $resource = EventResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['organizer_id'] = auth()->id();
        $data['created_by']   = auth()->id();

        return $data;
    }
}
