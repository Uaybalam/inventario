<?php

namespace App\Filament\Resources;

use App\Filament\Resources\InventarioResource\Pages;
use App\Filament\Resources\InventarioResource\RelationManagers;
use App\Models\Inventario;
use App\Models\ProductoSku;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Actions\BulkAction;
use Illuminate\Database\Eloquent\Collection; 

class InventarioResource extends Resource
{
    protected static ?string $model = Inventario::class;

    protected static ?string $navigationIcon = 'heroicon-o-archive-box';
    protected static ?string $navigationLabel = 'Inventario';

    public static function form(Form $form): Form
    {
        return $form
        ->schema([
            Select::make('id_producto_sku')
                ->relationship('producto', 'nombre')
                ->label('Producto')
                ->required(),
            TextInput::make('cantidad')->numeric()->required(),
            DatePicker::make('fecha_actualizacion')->required(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
        ->columns([
            TextColumn::make('producto.nombre')->label('Producto'),
            TextColumn::make('cantidad'),
            TextColumn::make('fecha_actualizacion')->date(),
            TextColumn::make('ubicacion.nombre'),
            TextColumn::make('estado.nombre'),
            TextColumn::make('responsable.nombre')->label('Responsable actual'),
        ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    BulkAction::make('asignarResponsable')
                    ->label('Asignar responsable')
                    ->icon('heroicon-m-user-plus')
                    ->action(function (Collection $records, array $data) {
                        foreach ($records as $record) {
                            $record->update(['id_responsable' => $data['id_responsable']]);
                        }
                    })
                    ->form([
                        Forms\Components\Select::make('id_responsable')
                            ->relationship('responsable', 'nombre') // Relación definida en Inventario
                            ->required()
                            ->searchable()
                            ->label('Selecciona un responsable'),
                    ])
                    ->deselectRecordsAfterCompletion(true),
                    // Acción masiva: Asignar ubicación
                BulkAction::make('asignarUbicacion')
                ->label('Asignar ubicación')
                ->icon('heroicon-m-map-pin')
                ->action(function (Collection $records, array $data) {
                    foreach ($records as $record) {
                        $record->update(['id_ubicacion' => $data['ubicacion']]);
                    }
                })
                ->form([
                    Forms\Components\Select::make('ubicacion')
                        ->relationship('ubicacion', 'nombre') // Usa la relación definida en Inventario
                        ->required()
                        ->searchable()
                        ->label('Selecciona una ubicación'),
                ])
                ->deselectRecordsAfterCompletion(true),

            // Acción masiva: Asignar estado
            BulkAction::make('asignarEstado')
                ->label('Asignar estado')
                ->icon('heroicon-m-tag')
                ->action(function (Collection $records, array $data) {
                    foreach ($records as $record) {
                        $record->update(['id_estado' => $data['estado']]);
                    }
                })
                ->form([
                    Forms\Components\Select::make('estado')
                    ->relationship('estado', 'nombre')
                    ->required()
                    ->searchable()
                    ->label('Selecciona un estado'),
                ])
                ->deselectRecordsAfterCompletion(true),
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
    public function productoSKU()
{
    return $this->belongsTo(ProductoSku::class, 'id_producto_sku');
}

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListInventarios::route('/'),
            'create' => Pages\CreateInventario::route('/create'),
            'edit' => Pages\EditInventario::route('/{record}/edit'),
        ];
    }
}
