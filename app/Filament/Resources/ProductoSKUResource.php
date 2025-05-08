<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductoSKUResource\Pages;
use App\Filament\Resources\ProductoSKUResource\RelationManagers;
use App\Models\ProductoSKU;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ProductoSKUResource extends Resource
{
    protected static ?string $model = ProductoSKU::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
        ->schema([
            TextInput::make('nombre')->required()->maxLength(100),
            Textarea::make('descripcion')->nullable(),
            Select::make('id_categoria')
                ->relationship('categoria', 'nombre')
                ->required()
                ->label('Categoría'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
        ->columns([
            TextColumn::make('nombre')->sortable()->searchable(),
            TextColumn::make('descripcion')->limit(50),
            TextColumn::make('categoria.nombre')->label('Categoría'),
        ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
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
            'index' => Pages\ListProductoSKUS::route('/'),
            'create' => Pages\CreateProductoSKU::route('/create'),
            'edit' => Pages\EditProductoSKU::route('/{record}/edit'),
        ];
    }
}
