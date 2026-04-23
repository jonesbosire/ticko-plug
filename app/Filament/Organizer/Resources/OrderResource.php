<?php

namespace App\Filament\Organizer\Resources;

use App\Enums\OrderStatus;
use App\Filament\Organizer\Resources\OrderResource\Pages;
use App\Models\Order;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;
    protected static ?string $navigationIcon = 'heroicon-o-shopping-bag';
    protected static ?string $navigationGroup = 'Attendees & Check-in';
    protected static ?int $navigationSort = 2;
    protected static ?string $recordTitleAttribute = 'order_number';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->whereHas('event', fn ($q) => $q->where('organizer_id', auth()->id()))
            ->with(['event', 'tickets']);
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Order Details')->schema([
                Forms\Components\TextInput::make('order_number')->disabled(),
                Forms\Components\TextInput::make('status')->disabled(),
                Forms\Components\TextInput::make('buyer_name')->disabled(),
                Forms\Components\TextInput::make('buyer_email')->disabled(),
                Forms\Components\TextInput::make('buyer_phone')->disabled(),
                Forms\Components\TextInput::make('total')->disabled()->prefix('KES'),
                Forms\Components\TextInput::make('mpesa_receipt_number')->disabled()->label('M-Pesa Receipt'),
                Forms\Components\DateTimePicker::make('paid_at')->disabled(),
            ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('order_number')
                    ->searchable()
                    ->fontFamily('mono')
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('event.title')
                    ->searchable()
                    ->limit(30),

                Tables\Columns\TextColumn::make('buyer_name')
                    ->searchable(),

                Tables\Columns\TextColumn::make('buyer_phone')
                    ->searchable(),

                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (OrderStatus $state) => match ($state) {
                        OrderStatus::Paid    => 'success',
                        OrderStatus::Pending, OrderStatus::Processing => 'warning',
                        OrderStatus::Failed, OrderStatus::Cancelled   => 'danger',
                        OrderStatus::Refunded, OrderStatus::PartiallyRefunded => 'info',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('total')
                    ->money('KES')
                    ->sortable(),

                Tables\Columns\TextColumn::make('tickets_count')
                    ->counts('tickets')
                    ->label('Tickets'),

                Tables\Columns\TextColumn::make('paid_at')
                    ->dateTime('M j, g:i A', 'Africa/Nairobi')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('event')
                    ->relationship('event', 'title', fn (Builder $q) => $q->where('organizer_id', auth()->id())),
                Tables\Filters\SelectFilter::make('status')
                    ->options(collect(OrderStatus::cases())->mapWithKeys(fn ($e) => [$e->value => ucfirst($e->value)])),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\Action::make('refund')
                    ->label('Refund')
                    ->icon('heroicon-o-arrow-uturn-left')
                    ->color('warning')
                    ->visible(fn (Order $record) => $record->status === OrderStatus::Paid)
                    ->requiresConfirmation()
                    ->form([
                        Forms\Components\Textarea::make('refund_reason')
                            ->required()
                            ->label('Reason for refund'),
                    ])
                    ->action(function (Order $record, array $data) {
                        $record->update([
                            'status'        => OrderStatus::Refunded,
                            'refund_reason' => $data['refund_reason'],
                            'refunded_at'   => now(),
                            'refunded_by'   => auth()->id(),
                        ]);
                        Notification::make()->title('Refund marked — process manually via M-Pesa reversal.')->warning()->send();
                    }),
            ])
            ->defaultSort('paid_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOrders::route('/'),
            'view'  => Pages\ViewOrder::route('/{record}'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }
}
