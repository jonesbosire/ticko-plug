<?php

namespace App\Filament\Organizer\Resources;

use App\Enums\EventStatus;
use App\Filament\Organizer\Resources\EventResource\Pages;
use App\Models\Category;
use App\Models\Event;
use App\Models\Venue;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class EventResource extends Resource
{
    protected static ?string $model = Event::class;
    protected static ?string $navigationIcon = 'heroicon-o-calendar';
    protected static ?string $navigationGroup = 'My Events';
    protected static ?int $navigationSort = 1;
    protected static ?string $recordTitleAttribute = 'title';

    // Scope: only show events belonging to current organizer
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('organizer_id', auth()->id())
            ->withoutGlobalScopes();
    }

    public static function form(Form $form): Form
    {
        return $form->schema([

            Forms\Components\Tabs::make()->tabs([

                // ── Basic Info ──────────────────────────────────────────
                Forms\Components\Tabs\Tab::make('Basic Info')
                    ->icon('heroicon-o-information-circle')
                    ->schema([
                        Forms\Components\TextInput::make('title')
                            ->required()
                            ->maxLength(200)
                            ->live(debounce: 500)
                            ->afterStateUpdated(fn ($state, Forms\Set $set) =>
                                $set('slug', Str::slug($state))
                            )
                            ->columnSpanFull(),

                        Forms\Components\TextInput::make('slug')
                            ->required()
                            ->unique(Event::class, 'slug', ignoreRecord: true)
                            ->maxLength(255)
                            ->columnSpanFull(),

                        Forms\Components\Textarea::make('tagline')
                            ->maxLength(300)
                            ->rows(2)
                            ->columnSpanFull(),

                        Forms\Components\RichEditor::make('description')
                            ->required()
                            ->toolbarButtons(['bold', 'italic', 'bulletList', 'orderedList', 'link', 'h2', 'h3'])
                            ->columnSpanFull(),

                        Forms\Components\Select::make('category_id')
                            ->label('Category')
                            ->options(Category::where('is_active', true)->pluck('name', 'id'))
                            ->required()
                            ->searchable(),

                        Forms\Components\TagsInput::make('tags')
                            ->placeholder('Add tags…')
                            ->separator(','),
                    ])->columns(2),

                // ── Date & Venue ────────────────────────────────────────
                Forms\Components\Tabs\Tab::make('Date & Venue')
                    ->icon('heroicon-o-map-pin')
                    ->schema([
                        Forms\Components\DateTimePicker::make('start_datetime')
                            ->required()
                            ->native(false)
                            ->minutesStep(15)
                            ->timezone('Africa/Nairobi')
                            ->label('Start Date & Time'),

                        Forms\Components\DateTimePicker::make('end_datetime')
                            ->required()
                            ->native(false)
                            ->minutesStep(15)
                            ->timezone('Africa/Nairobi')
                            ->label('End Date & Time')
                            ->after('start_datetime'),

                        Forms\Components\DateTimePicker::make('doors_open_at')
                            ->native(false)
                            ->minutesStep(15)
                            ->timezone('Africa/Nairobi')
                            ->label('Doors Open At'),

                        Forms\Components\Toggle::make('is_online')
                            ->label('This is an online event')
                            ->live(),

                        Forms\Components\TextInput::make('online_event_url')
                            ->label('Online Event URL')
                            ->url()
                            ->visible(fn (Forms\Get $get) => $get('is_online')),

                        Forms\Components\Select::make('venue_id')
                            ->label('Venue')
                            ->options(Venue::all()->pluck('name', 'id'))
                            ->searchable()
                            ->createOptionForm([
                                Forms\Components\TextInput::make('name')->required(),
                                Forms\Components\TextInput::make('address')->required(),
                                Forms\Components\TextInput::make('city')->required(),
                                Forms\Components\TextInput::make('country')->default('Kenya'),
                            ])
                            ->visible(fn (Forms\Get $get) => ! $get('is_online')),
                    ])->columns(2),

                // ── Media ───────────────────────────────────────────────
                Forms\Components\Tabs\Tab::make('Media')
                    ->icon('heroicon-o-photo')
                    ->schema([
                        Forms\Components\SpatieMediaLibraryFileUpload::make('banner')
                            ->collection('banner')
                            ->image()
                            ->imageResizeMode('cover')
                            ->imageResizeTargetWidth('1920')
                            ->imageResizeTargetHeight('1080')
                            ->maxSize(5 * 1024)
                            ->label('Event Banner (16:9 recommended)')
                            ->columnSpanFull(),

                        Forms\Components\SpatieMediaLibraryFileUpload::make('images')
                            ->collection('images')
                            ->image()
                            ->multiple()
                            ->maxFiles(10)
                            ->label('Additional Images')
                            ->columnSpanFull(),
                    ]),

                // ── Settings ────────────────────────────────────────────
                Forms\Components\Tabs\Tab::make('Settings')
                    ->icon('heroicon-o-cog')
                    ->schema([
                        Forms\Components\Select::make('visibility')
                            ->options([
                                'public'   => 'Public',
                                'private'  => 'Private (link only)',
                                'unlisted' => 'Unlisted',
                            ])
                            ->default('public')
                            ->required(),

                        Forms\Components\TextInput::make('min_age')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(99)
                            ->label('Minimum Age (leave blank for all ages)'),

                        Forms\Components\TextInput::make('dress_code')
                            ->maxLength(100),

                        Forms\Components\Toggle::make('organizer_absorbs_fee')
                            ->label('I will absorb the platform fee (ticket price is all-inclusive)')
                            ->helperText('When enabled, the platform fee is deducted from your payout instead of added to the ticket price.'),
                    ])->columns(2),

            ])->columnSpanFull(),

        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\SpatieMediaLibraryImageColumn::make('banner')
                    ->collection('banner')
                    ->width(80)
                    ->height(50)
                    ->extraImgAttributes(['class' => 'rounded-lg object-cover'])
                    ->label(''),

                Tables\Columns\TextColumn::make('title')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('start_datetime')
                    ->label('Date')
                    ->dateTime('M j, Y · g:i A', 'Africa/Nairobi')
                    ->sortable(),

                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (EventStatus $state) => $state->getColor())
                    ->icon(fn (EventStatus $state) => $state->getIcon()),

                Tables\Columns\TextColumn::make('total_tickets_sold')
                    ->label('Sold')
                    ->numeric()
                    ->sortable(),

                Tables\Columns\TextColumn::make('total_revenue')
                    ->label('Revenue')
                    ->money('KES')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options(collect(EventStatus::cases())->mapWithKeys(fn ($e) => [$e->value => $e->getLabel()])),
            ])
            ->actions([
                Tables\Actions\Action::make('publish')
                    ->label('Publish')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (Event $record) => $record->status === EventStatus::Draft)
                    ->requiresConfirmation()
                    ->action(function (Event $record) {
                        $record->update([
                            'status'       => EventStatus::Published,
                            'published_at' => now(),
                        ]);
                        Notification::make()
                            ->title('Event published!')
                            ->success()
                            ->send();
                    }),

                Tables\Actions\Action::make('scan')
                    ->label('Check-In')
                    ->icon('heroicon-o-qr-code')
                    ->color('warning')
                    ->url(fn (Event $record) => route('scan.show', $record))
                    ->openUrlInNewTab()
                    ->visible(fn (Event $record) => $record->status === EventStatus::Published),

                Tables\Actions\EditAction::make(),

                Tables\Actions\Action::make('cancel')
                    ->label('Cancel Event')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn (Event $record) => in_array($record->status->value, ['draft', 'published']))
                    ->requiresConfirmation()
                    ->form([
                        Forms\Components\Textarea::make('cancellation_reason')
                            ->required()
                            ->label('Reason for cancellation'),
                    ])
                    ->action(function (Event $record, array $data) {
                        $record->update([
                            'status'              => EventStatus::Cancelled,
                            'cancelled_at'        => now(),
                            'cancellation_reason' => $data['cancellation_reason'],
                        ]);
                        Notification::make()->title('Event cancelled')->warning()->send();
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('start_datetime', 'desc')
            ->emptyStateHeading('No events yet')
            ->emptyStateDescription('Create your first event to start selling tickets.')
            ->emptyStateActions([
                Tables\Actions\CreateAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
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
