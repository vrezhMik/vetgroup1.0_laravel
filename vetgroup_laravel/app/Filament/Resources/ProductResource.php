<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductResource\Pages;
use App\Models\Product;
use Illuminate\Database\Eloquent\Builder;
use BackedEnum;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Storage;
use UnitEnum;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-archive-box';

    protected static ?string $navigationLabel = 'Products';

    protected static UnitEnum|string|null $navigationGroup = 'Vetgroup';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('code')
                    ->maxLength(255)
                    ->nullable(),
                Forms\Components\Textarea::make('description')
                    ->nullable(),
                Forms\Components\TextInput::make('price')
                    ->numeric()
                    ->nullable(),
                Forms\Components\TextInput::make('backend_id')
                    ->maxLength(255)
                    ->nullable(),
                Forms\Components\TextInput::make('stock')
                    ->integer()
                    ->nullable(),
                Forms\Components\TextInput::make('pack_price')
                    ->numeric()
                    ->nullable(),
                Forms\Components\Select::make('categories')
                    ->label('Categories')
                    ->relationship('categories', 'title')
                    ->multiple()
                    ->preload()
                    ->searchable()
                    ->createOptionForm([
                        Forms\Components\TextInput::make('title')
                            ->required()
                            ->maxLength(255),
                    ]),
                Forms\Components\ViewField::make('image_preview')
                    ->label('Current Image')
                    ->view('filament.forms.components.product-image-preview'),
                Forms\Components\FileUpload::make('image')
                    ->image()
                    ->disk('uploads')
                    ->directory('')
                    ->nullable(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('image')
                    ->label('Image')
                    ->getStateUsing(function (Product $record): ?string {
                        if (! $record->image) {
                            return null;
                        }

                        // Normalize path so we don't end up with "uploads/uploads/..."
                        $path = ltrim($record->image, '/');
                        $path = preg_replace('#^uploads/#', '', $path);

                        return Storage::disk('uploads')->url($path);
                    })
                    ->size(32),
                Tables\Columns\TextColumn::make('id')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('name')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('categories.title')
                    ->label('Category')
                    ->badge()
                    ->separator(', '),
                Tables\Columns\TextColumn::make('code')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('price')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('stock')
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
            'index' => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with('categories');
    }
}
