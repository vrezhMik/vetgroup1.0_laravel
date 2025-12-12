<?php

namespace App\Filament\Resources;

use App\Filament\Resources\VetgroupUserResource\Pages;
use App\Models\VetgroupUser;
use BackedEnum;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use UnitEnum;

class VetgroupUserResource extends Resource
{
    protected static ?string $model = VetgroupUser::class;

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-user-group';

    protected static ?string $navigationLabel = 'Vetgroup Users';

    protected static UnitEnum|string|null $navigationGroup = 'Vetgroup';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Forms\Components\TextInput::make('username')
                    ->maxLength(255)
                    ->nullable(),
                Forms\Components\TextInput::make('email')
                    ->email()
                    ->maxLength(255)
                    ->nullable(),
                Forms\Components\TextInput::make('password')
                    ->password()
                    ->maxLength(255)
                    ->nullable(),
                Forms\Components\TextInput::make('company')
                    ->maxLength(255)
                    ->nullable(),
                Forms\Components\TextInput::make('first_name')
                    ->maxLength(255)
                    ->nullable(),
                Forms\Components\TextInput::make('last_name')
                    ->maxLength(255)
                    ->nullable(),
                Forms\Components\TextInput::make('location')
                    ->maxLength(255)
                    ->nullable(),
                Forms\Components\TextInput::make('code')
                    ->maxLength(255)
                    ->nullable(),
                Forms\Components\TextInput::make('user_id')
                    ->label('User ID')
                    ->maxLength(255)
                    ->nullable(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('username')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('email')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('company')
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                EditAction::make(),
            ])
            ->bulkActions([
                DeleteBulkAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListVetgroupUsers::route('/'),
            'create' => Pages\CreateVetgroupUser::route('/create'),
            'edit' => Pages\EditVetgroupUser::route('/{record}/edit'),
        ];
    }
}
