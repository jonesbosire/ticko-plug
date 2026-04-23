<?php

namespace App\Filament\Organizer\Resources;

use App\Filament\Organizer\Resources\PromoCodeResource\Pages;
use App\Models\Event;
use App\Models\PromoCode;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class PromoCodeResource extends Resource
{
    protected static ?string $model = PromoCode::class;
    protected static ?string $navigationIcon = 'heroicon-o-tag';
    protected static ?string $navigationGroup = 'My Events';
    protected static ?int $navigationSort = 3;

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->whereHas('event', fn ($q) => $q->where('organizer_id', auth()->id()));
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make()->schema([

                Forms\Components\Select::make('event_id')
                    ->label('Event')
                    ->options(
                        Event::where('organizer_id', auth()->id())
                            ->pluck('title', 'id')
                    )
                    ->required()
                    ->searchable(),

                Forms\Components\TextInput::make('code')
                    ->required()
                    ->maxLength(50)
                    ->default(fn () => strtoupper(Str::random(8)))
                    ->suffixAction(
                        Forms\Components\Actions\Action::make('generate')
                            ->icon('heroicon-o-arrow-path')
                            ->action(fn (Forms\Set $set) => $set('code', strtoupper(Str::random(8))))
                    )
                    ->extraInputAttributes(['class' => 'uppercase tracking-widest font-mono']),

                Forms\Components\Select::make('discount_type')
                    ->options([
                        'percentage' => 'Percentage (%)',
                        'fixed'      => 'Fixed Amount (KES)',
                    ])
                    ->required()
                    ->default('percentage')
                    ->live(),

                Forms\Components\TextInput::make('discount_value')
                    ->numeric()
                    ->required()
                    ->minValue(1)
                    ->suffix(fn (Forms\Get $get) => $get('discount_type') === 'percentage' ? '%' : 'KES'),

                Forms\Components\TextInput::make('usage_limit')
                    ->numeric()
                    ->minValue(1)
                    ->label('Max Uses (leave blank for unlimited)'),

                Forms\Components\TextInput::make('min_order_amount')
                    ->numeric()
                    ->prefix('KES')
                    ->label('Minimum Order Amount'),

                Forms\Components\DateTimePicker::make('starts_at')
                    ->timezone('Africa/Nairobi')
                    ->native(false)
                    ->label('Sale Start'),

                Forms\Components\DateTimePicker::make('expires_at')
                    ->timezone('Africa/Nairobi')
                    ->native(false)
                    ->label('Expiry Date'),

                Forms\Components\Toggle::make('is_active')
                    ->default(true)
                    ->label('Active'),

            ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('code')
                    ->searchable()
                    ->fontFamily('mono')
                    ->weight('bold')
                    ->copyable(),

                Tables\Columns\TextColumn::make('event.title')
                    ->limit(25),

                Tables\Columns\TextColumn::make('discount_type')
                    ->badge()
                    ->color(fn (string $state) => $state === 'percentage' ? 'success' : 'info'),

                Tables\Columns\TextColumn::make('discount_value')
                    ->formatStateUsing(fn ($state, PromoCode $record) =>
                        $record->discount_type === 'percentage'
                            ? $state . '%'
                            : 'KES ' . number_format($state)
                    ),

                Tables\Columns\TextColumn::make('times_used')
                    ->label('Used')
                    ->formatStateUsing(fn ($state, PromoCode $record) =>
                        $state . ($record->usage_limit ? ' / ' . $record->usage_limit : '')
                    ),

                Tables\Columns\IconColumn::make('is_active')
                    ->boolean()
                    ->label('Active'),

                Tables\Columns\TextColumn::make('expires_at')
                    ->dateTime('M j, Y', 'Africa/Nairobi')
                    ->label('Expires'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->emptyStateHeading('No promo codes')
            ->emptyStateDescription('Create a promo code to give discounts to attendees.');
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListPromoCodes::route('/'),
            'create' => Pages\CreatePromoCode::route('/create'),
            'edit'   => Pages\EditPromoCode::route('/{record}/edit'),
        ];
    }
}
