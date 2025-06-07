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
use Filament\Tables\Actions\Action;
use Illuminate\Support\Facades\Route;
use Filament\Forms\Components\Repeater;
use App\Models\ArticuloInventario;
use Illuminate\Support\Facades\DB;
use App\Exports\InventariosExport;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\Subgrupo;
use App\Models\Grupo;
use App\Models\Clase;
use App\Models\Subclase;

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
            Select::make('id_grupo')
            ->relationship('grupo', 'nombre')
            ->searchable()
            ->reactive()
            ->afterStateUpdated(fn (callable $set) => $set('id_subgrupo', null)),

        Select::make('id_subgrupo')
            ->label('Subgrupo')
            ->options(function (callable $get) {
                if (!$get('id_grupo')) {
                    return [];
                }

                return Subgrupo::where('id_grupo', $get('id_grupo'))->pluck('nombre', 'id_subgrupo');
            })
            ->searchable()
            ->reactive()
            ->afterStateUpdated(fn (callable $set) => $set('id_clase', null)),

        Select::make('id_clase')
            ->label('Clase')
            ->options(function (callable $get) {
                if (!$get('id_subgrupo')) {
                    return [];
                }

                return Clase::where('id_subgrupo', $get('id_subgrupo'))->pluck('nombre', 'id_clase');
            })
            ->searchable()
            ->reactive()
            ->afterStateUpdated(fn (callable $set) => $set('id_subclase', null)),

        Select::make('id_subclase')
            ->label('Subclase')
            ->options(function (callable $get) {
                if (!$get('id_clase')) {
                    return [];
                }

                return Subclase::where('id_clase', $get('id_clase'))->pluck('nombre', 'id_subclase');
            })
            ->searchable()
            ->label('Subclase')
            ->afterStateUpdated(function (callable $get, callable $set) {
                $grupoClave = DB::table('grupos')->where('id_grupo', $get('id_grupo'))->value('clave');
                $subgrupoClave = DB::table('subgrupos')->where('id_subgrupo', $get('id_subgrupo'))->value('clave');
                $claseClave = DB::table('clases')->where('id_clase', $get('id_clase'))->value('clave');
                $subclaseClave = DB::table('subclases')->where('id_subclase', $get('id_subclase'))->value('clave');
            
                if ($grupoClave && $subgrupoClave && $claseClave && $subclaseClave) {
                    $set('cog', "{$grupoClave}{$subgrupoClave}{$claseClave}{$subclaseClave}");
                }
            }),

        TextInput::make('cog')
        ->label('COG')
    ->dehydrated()
    ->reactive()
    ->afterStateUpdated(function (callable $set, $state) {
        if (strlen($state) === 4) {
            $grupoClave = $state[0];
            $subgrupoClave = $state[1];
            $claseClave = $state[2];
            $subclaseClave = $state[3];

            $idGrupo = DB::table('grupos')->where('clave', $grupoClave)->value('id_grupo');
            $idSubgrupo = DB::table('subgrupos')->where('clave', $subgrupoClave)->where('id_grupo', $idGrupo)->value('id_subgrupo');
            $idClase = DB::table('clases')->where('clave', $claseClave)->where('id_subgrupo', $idSubgrupo)->value('id_clase');
            $idSubclase = DB::table('subclases')->where('clave', $subclaseClave)->where('id_clase', $idClase)->value('id_subclase');

            $set('id_grupo', $idGrupo);
            $set('id_subgrupo', $idSubgrupo);
            $set('id_clase', $idClase);
            $set('id_subclase', $idSubclase);
        }
    }),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
        ->columns([
            TextColumn::make('producto.nombre')->label('Producto')->searchable(),
            TextColumn::make('cantidad')->searchable(),
            TextColumn::make('fecha_actualizacion')->dateTime()->label('Fecha de Creacion')->searchable(),
            TextColumn::make('ubicacion.nombre')->searchable(),
            TextColumn::make('estado.nombre')->searchable(),
            TextColumn::make('responsable.nombre')->label('Responsable actual')->searchable(),
            TextColumn::make('cog')->label('COG')->searchable(),
        ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Action::make('generarResponsiva')
    ->label('Generar Responsiva')
    ->icon('heroicon-o-document')
    ->action(function ($record) {
        return redirect()->route('pdf.responsiva', ['responsable' => $record->id_responsable]);
    })
    ->visible(fn ($record) => $record->id_responsable !== null),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    BulkAction::make('exportarAExcel')
                    ->label('Exportar a Excel')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->action(function (Collection $records) {
                        return Excel::download(new InventariosExport($records), 'inventarios_export_' . now()->format('Ymd_His') . '.xlsx');
                    })
                    ->deselectRecordsAfterCompletion(true),
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
                            ->relationship('responsable', 'nombre') 
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
                        ->relationship('ubicacion', 'nombre') 
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
           
        ];
    }
    public function producto()
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
