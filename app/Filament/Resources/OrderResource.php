<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrderResource\Pages;
use App\Models\Order;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class OrderResource extends Resource
{
    protected static ?string $model           = Order::class;
    protected static ?string $navigationIcon  = 'heroicon-o-shopping-bag';
    protected static ?string $navigationGroup = 'Finance';
    protected static ?int    $navigationSort  = 1;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Order Info')->schema([
                Forms\Components\TextInput::make('order_number')->disabled(),
                Forms\Components\TextInput::make('status')->disabled(),
                Forms\Components\TextInput::make('buyer_name')->disabled(),
                Forms\Components\TextInput::make('buyer_email')->disabled(),
                Forms\Components\TextInput::make('buyer_phone')->disabled(),
                Forms\Components\TextInput::make('total')->disabled()->prefix('KES'),
                Forms\Components\TextInput::make('payment_method')->disabled(),
                Forms\Components\TextInput::make('mpesa_receipt_number')->label('M-Pesa Receipt')->disabled(),
            ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('order_number')
                    ->searchable()
                    ->copyable()
                    ->fontFamily('mono'),
                Tables\Columns\TextColumn::make('event.title')
                    ->label('Event')
                    ->searchable()
                    ->limit(35),
                Tables\Columns\TextColumn::make('buyer_name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('buyer_phone')
                    ->searchable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn ($state) => match ($state) {
                        'paid'               => 'success',
                        'pending'            => 'warning',
                        'processing'         => 'info',
                        'failed'             => 'danger',
                        'refunded'           => 'gray',
                        'partially_refunded' => 'warning',
                        'cancelled'          => 'danger',
                        default              => 'gray',
                    }),
                Tables\Columns\TextColumn::make('total')
                    ->money('KES')
                    ->sortable(),
                Tables\Columns\TextColumn::make('payment_method')
                    ->badge(),
                Tables\Columns\TextColumn::make('mpesa_receipt_number')
                    ->label('M-Pesa Ref')
                    ->searchable()
                    ->copyable()
                    ->placeholder('—'),
                Tables\Columns\TextColumn::make('paid_at')
                    ->label('Paid At')
                    ->dateTime('d M Y, H:i')
                    ->sortable()
                    ->placeholder('—'),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime('d M Y, H:i')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending'            => 'Pending',
                        'processing'         => 'Processing',
                        'paid'               => 'Paid',
                        'failed'             => 'Failed',
                        'cancelled'          => 'Cancelled',
                        'refunded'           => 'Refunded',
                        'partially_refunded' => 'Partially Refunded',
                    ]),
                Tables\Filters\SelectFilter::make('payment_method')
                    ->options([
                        'mpesa' => 'M-Pesa',
                        'card'  => 'Card',
                        'free'  => 'Free',
                    ]),
                Tables\Filters\Filter::make('paid_today')
                    ->label('Paid Today')
                    ->query(fn ($query) => $query->where('status', 'paid')
                        ->whereDate('paid_at', today())),
            ])
            ->actions([
                Tables\Actions\Action::make('refund')
                    ->label('Refund')
                    ->icon('heroicon-o-arrow-uturn-left')
                    ->color('warning')
                    ->visible(fn ($record) => $record->status === 'paid')
                    ->requiresConfirmation()
                    ->form([
                        Forms\Components\Textarea::make('refund_reason')
                            ->label('Reason for refund')
                            ->required(),
                    ])
                    ->action(function ($record, array $data, $livewire) {
                        $record->update([
                            'status'        => 'refunded',
                            'refunded_at'   => now(),
                            'refunded_by'   => auth()->id(),
                            'refund_reason' => $data['refund_reason'],
                        ]);
                        Notification::make()
                            ->title('Order marked as refunded. Process manual reversal via payment gateway.')
                            ->warning()->send();
                    }),
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([]),
            ])
            ->defaultSort('created_at', 'desc');
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
