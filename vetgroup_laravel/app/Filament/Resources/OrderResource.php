<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrderResource\Pages;
use App\Models\Order;
use BackedEnum;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use UnitEnum;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-shopping-cart';

    protected static ?string $navigationLabel = 'Orders';

    protected static UnitEnum|string|null $navigationGroup = 'Vetgroup';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Forms\Components\TextInput::make('order_id')
                    ->label('Order ID')
                    ->maxLength(255)
                    ->nullable(),
                Forms\Components\TextInput::make('vetgroup_user_id')
                    ->label('Vetgroup User ID')
                    ->numeric()
                    ->nullable(),
                Forms\Components\DateTimePicker::make('created')
                    ->label('Order Date')
                    ->nullable(),
                Forms\Components\TextInput::make('total')
                    ->numeric()
                    ->nullable(),
                Forms\Components\Textarea::make('products')
                    ->nullable(),
                Forms\Components\CodeEditor::make('products_json')
                    ->label('Products JSON')
                    ->json()
                    ->columnSpanFull()
                    ->nullable(),
                Forms\Components\ViewField::make('products_table')
                    ->view('filament.forms.components.order-products-table'),
                Forms\Components\Toggle::make('complited')
                    ->label('Completed')
                    ->nullable(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('order_id')
                    ->label('Order ID')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('user.company')
                    ->label('User')
                    ->url(fn (Order $record) => $record->vetgroup_user_id ? route('filament.admin.resources.vetgroup-users.edit', ['record' => $record->vetgroup_user_id]) : null)
                    ->openUrlInNewTab()
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('total')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\IconColumn::make('complited')
                    ->boolean()
                    ->label('Completed'),
                Tables\Columns\TextColumn::make('created')
                    ->label('Order Date')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
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
            'index' => Pages\ListOrders::route('/'),
            'create' => Pages\CreateOrder::route('/create'),
            'edit' => Pages\EditOrder::route('/{record}/edit'),
        ];
    }
}
