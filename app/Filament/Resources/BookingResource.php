<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BookingResource\Pages;
use App\Filament\Resources\BookingResource\RelationManagers;
use App\Models\Booking;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class BookingResource extends Resource
{
    protected static ?string $model = Booking::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-cart';
    public static function canCreate(): bool
    {
        return false;  
    }
    public static function table(Table $table): Table
{
    return $table
            ->columns([
            Tables\Columns\TextColumn::make('user.name')->label('User'),
            Tables\Columns\TextColumn::make('event.title')->label('Event'),
            Tables\Columns\TextColumn::make('ticket.name')->label('Ticket'),
            Tables\Columns\TextColumn::make('quantity'),
            Tables\Columns\TextColumn::make('total_price')->money('IDR'),
            Tables\Columns\TextColumn::make('status')->badge(),
            Tables\Columns\TextColumn::make('created_at')->dateTime('d M Y'),
        ])
    
        ->filters([
            Tables\Filters\SelectFilter::make('status')->options([
                'pending' => 'Pending',
                'confirmed' => 'Confirmed',
                'cancelled' => 'Cancelled',
            ])->default('pending'),
        ])
        ->actions([
           
            Tables\Actions\Action::make('approve')
                ->label('Approve')
                ->color('success')
                ->icon('heroicon-o-check')
                ->visible(fn ($record) => $record->status === 'pending')
                ->action(function ($record) {
                    $record->update(['status' => 'confirmed']);
                }),

           
            Tables\Actions\Action::make('reject')
                ->label('Reject')
                ->color('danger')
                ->icon('heroicon-o-x-mark')
                ->visible(fn ($record) => $record->status === 'pending')
                ->action(function ($record) {
                    $record->update(['status' => 'cancelled']);
                }),
        ])
        ->bulkActions([
            Tables\Actions\BulkActionGroup::make([
                Tables\Actions\DeleteBulkAction::make(),
            ]),
        ]);
}


    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBookings::route('/'),
            // 'create' => Pages\CreateBooking::route('/create'),
            'edit' => Pages\EditBooking::route('/{record}/edit'),
        ];
    }
}
