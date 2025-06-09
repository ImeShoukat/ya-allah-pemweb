<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EventResource\Pages;
use App\Filament\Resources\EventResource\RelationManagers;
use App\Models\Event;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Illuminate\Support\Facades\Auth;

class EventResource extends Resource
{
    protected static ?string $model = Event::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('organizer_id')
                ->label('Organizer')
                ->relationship('organizer', 'name') // pastikan Organizer punya kolom `name`
                ->searchable()
                ->required(),

                TextInput::make('organizer.user.name')
                ->label('Pengaju')
                ->disabled()
                ->default(Auth::user()?->name),
                Forms\Components\TextInput::make('title')->required(),
                Forms\Components\Textarea::make('description')->required(),
                Forms\Components\TextInput::make('location')->required(),
                Forms\Components\DateTimePicker::make('start_date')->required(),
                Forms\Components\DateTimePicker::make('end_date')->required(),
                Forms\Components\FileUpload::make('banner_image')
                    ->disk('public') 
                    ->directory('event-banners') 
                    ->image()
                    ->nullable(),
                Forms\Components\Select::make('category')
                ->options([
                    'festival'=> 'Festival',
                    'konser' => 'Konser',
                    'expo' => 'Expo',
                    'volunteer'=>'Volunteer',
                ])
                ->required(),
                Forms\Components\Textarea::make('policies')->nullable(),
                Forms\Components\Toggle::make('is_published')->label('Publish?'),
                Forms\Components\Select::make('status')
                ->options([
                    'pending' => 'Pending',
                    'approved' => 'Approved',
                    'rejected' => 'Rejected',
                ])
                ->default('pending')
                ->disabled(fn () => auth()->user()->role !== 'admin'), // hanya admin bisa ubah status
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('title')->searchable()->sortable(),
            TextColumn::make('category'),
            TextColumn::make('organizer.user.name')
            ->label('Pengaju')
            ->searchable()
            ->sortable(),
            BadgeColumn::make('status')
            ->colors([
                'warning'=> 'pending',
                'success' => 'approved',
                'danger' => 'rejected',
            ]),
            TextColumn::make('start_date')->label('Start')->dateTime(),
            TextColumn::make('end_date')->label('End')->dateTime(),
        ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('approve')
                    ->label('Approve')
                    ->visible(fn ($record) => $record->status === 'pending')
                    ->action(function ($record) {
                        $record->status = 'approved';
                        $record->save();
                    })
                    ->successNotificationTitle('Event approved.')
                    ->requiresConfirmation()
                    ->after(fn () => redirect(request()->header('Referer'))),
                Tables\Actions\Action::make('reject')
                    ->label('Reject')
                    ->color('danger')
                    ->visible(fn ($record) => $record->status === 'pending')
                    ->action(function ($record) {
                        $record->status = 'rejected';
                        $record->save();
                    })
                    ->successNotificationTitle('Event rejected.')
                    ->requiresConfirmation()
                    ->after(fn () => redirect(request()->header('Referer'))),
                
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
            'index' => Pages\ListEvents::route('/'),
            'create' => Pages\CreateEvent::route('/create'),
            'edit' => Pages\EditEvent::route('/{record}/edit'),
        ];
    }
}

