<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TransaccionResource\Pages;
use App\Filament\Resources\TransaccionResource\RelationManagers;
use App\Models\Transaccion;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Models\ProductoSku;
use App\Models\User;

class TransaccionResource extends Resource
{
    protected static ?string $model = Transaccion::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationLabel = 'Transacciones';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('id_producto_sku')
                ->relationship('productoSku', 'nombre')
                ->required()
                ->label('Producto'),

            Forms\Components\Select::make('id_usuario')
                ->relationship('usuario', 'name')
                ->required()
                ->label('Responsable'),

            Forms\Components\Select::make('tipo')
                ->options([
                    'entrada' => 'Entrada',
                    'salida' => 'Salida',
                ])
                ->required(),

            Forms\Components\TextInput::make('cantidad')
                ->numeric()
                ->required(),

            Forms\Components\Textarea::make('descripcion')
                ->nullable(),

            Forms\Components\TextInput::make('ubicacion')
                ->label('Ubicación actual')
                ->nullable(),
                //
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('productoSku.nombre')->label('Producto'),
            Tables\Columns\TextColumn::make('usuario.name')->label('Responsable'),
            Tables\Columns\TextColumn::make('tipo')->label('Tipo'),
            Tables\Columns\TextColumn::make('cantidad')->label('Cantidad'),
            Tables\Columns\TextColumn::make('ubicacion')->label('Ubicación'),
            Tables\Columns\TextColumn::make('created_at')->label('Fecha')->date(),
            Tables\Columns\TextColumn::make('campo_modificado')->label('Campo'),
            Tables\Columns\TextColumn::make('valor_anterior')->label('Anterior'),
            Tables\Columns\TextColumn::make('valor_nuevo')->label('Nuevo'),
                
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
            'index' => Pages\ListTransaccions::route('/'),
            'create' => Pages\CreateTransaccion::route('/create'),
            'edit' => Pages\EditTransaccion::route('/{record}/edit'),
        ];
    }
}
