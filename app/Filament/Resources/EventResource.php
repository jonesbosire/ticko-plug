<?php

namespace App\Filament\Resources;

use App\Enums\EventStatus;
use App\Filament\Resources\EventResource\Pages;
use App\Models\Category;
use App\Models\Event;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class EventResource extends Resource
{
    protected static ?string $model            = Event::class;
    protected static ?string $navigationIcon   = 'heroicon-o-calendar-days';
    protected static ?string $navigationGroup  = 'Events';
    protected static ?int    $navigationSort   = 1;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Event Details')->schema([
                Forms\Components\TextInput::make('title')->required()->maxLength(200)->columnSpanFull(),
                Forms\Components\Select::make('status')
                    ->options(collect(EventStatus::cases())->mapWithKeys(fn ($s) => [$s->value => ucfirst($s->value)]))
                    ->required(),
                Forms\Components\Select::make('visibility')
                    ->options(['public' => 'Public', 'private' => 'Private', 'unlisted' => 'Unlisted'])
                    ->required(),
                Forms\Components\TextInput::make('cancellation_reason')
                    ->label('Cancellation Reason')
                    ->visible(fn ($get) => $get('status') === 'cancelled')
                    ->columnSpanFull(),
            ])->columns(2),

            Forms\Components\Section::make('Organizer')->schema([
                Forms\Components\Select::make('organizer_id')
                    ->relationship('organizer', 'name')
                    ->searchable()
                    ->required(),
            ]),

            Forms\Components\Section::make('Featured')->schema([
                Forms\Components\Toggle::make('featured')
                    ->label('Feature on Homepage')
                    ->afterStateHydrated(fn ($component, $state, $record) =>
                        $component->state($record && $record->featured_at !== null))
                    ->dehydrated(false),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->searchable()
                    ->sortable()
                    ->limit(40),
                Tables\Columns\TextColumn::make('organizer.name')
                    ->label('Organizer')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('category.name')
                    ->label('Category')
                    ->badge(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn ($state) => match ($state->value ?? $state) {
                        'published'  => 'success',
                        'draft'      => 'gray',
                        'cancelled'  => 'danger',
                        'postponed'  => 'warning',
                        'completed'  => 'info',
                        default      => 'gray',
                    }),
                Tables\Columns\TextColumn::make('start_datetime')
                    ->label('Date')
                    ->dateTime('D d M Y, H:i')
                    ->sortable(),
                Tables\Columns\TextColumn::make('total_tickets_sold')
                    ->label('Sold')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('total_revenue')
                    ->label('Revenue')
                    ->money('KES')
                    ->sortable(),
                Tables\Columns\IconColumn::make('featured_at')
                    ->label('Featured')
                    ->boolean()
                    ->trueIcon('heroicon-o-star')
                    ->falseIcon('heroicon-o-star'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options(collect(EventStatus::cases())->mapWithKeys(fn ($s) => [$s->value => ucfirst($s->value)])),
                Tables\Filters\SelectFilter::make('category')
                    ->relationship('category', 'name'),
            ])
            ->actions([
                Tables\Actions\Action::make('feature')
                    ->label(fn ($record) => $record->featured_at ? 'Unfeature' : 'Feature')
                    ->icon(fn ($record) => $record->featured_at ? 'heroicon-o-star' : 'heroicon-o-star')
                    ->color(fn ($record) => $record->featured_at ? 'warning' : 'success')
                    ->action(function ($record) {
                        $record->update(['featured_at' => $record->featured_at ? null : now()]);
                        Notification::make()
                            ->title($record->featured_at ? 'Event featured' : 'Event unfeatured')
                            ->success()->send();
                    }),

                Tables\Actions\Action::make('cancel')
                    ->label('Cancel')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn ($record) => ! in_array($record->status->value, ['cancelled', 'completed']))
                    ->requiresConfirmation()
                    ->form([
                        Forms\Components\Textarea::make('cancellation_reason')
                            ->label('Reason for cancellation')
                            ->required(),
                    ])
                    ->action(function ($record, array $data) {
                        $record->update([
                            'status'               => EventStatus::Cancelled,
                            'cancelled_at'         => now(),
                            'cancellation_reason'  => $data['cancellation_reason'],
                        ]);
                        Notification::make()->title('Event cancelled')->warning()->send();
                    }),

                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListEvents::route('/'),
            'create' => Pages\CreateEvent::route('/create'),
            'edit'   => Pages\EditEvent::route('/{record}/edit'),
        ];
    }
}
