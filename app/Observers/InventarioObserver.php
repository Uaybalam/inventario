<?php

namespace App\Observers;

use App\Models\Inventario;
use App\Models\Transaccion;

class InventarioObserver
{
    /**
     * Handle the Inventario "created" event.
     */
    public function created(Inventario $inventario): void
    {
        //
    }

    /**
     * Handle the Inventario "updated" event.
     */
    public function updated(Inventario $inventario): void
    {
        $changes = $inventario->getChanges();
        
        foreach($changes as $field => $newValue){
            if (in_array($field, ['ubicacion', 'estado', 'id_usuario'])) {
                Transaccion::create([
                    'id_producto_sku' => $inventario->id_producto_sku,
                    'id_usuario' => Auth::id(),
                    'tipo' => 'actualizacion',
                    'campo_modificado' => $field,
                    'valor_anterior' => $inventario->getOriginal($field),
                    'valor_nuevo' => $newValue,
                    'descripcion' => "Se actualizó el campo '$field'",
                    'ubicacion' => $inventario->ubicacion,
                ]);
            }
        }
    }

    /**
     * Handle the Inventario "deleted" event.
     */
    public function deleted(Inventario $inventario): void
    {
        //
    }

    /**
     * Handle the Inventario "restored" event.
     */
    public function restored(Inventario $inventario): void
    {
        //
    }

    /**
     * Handle the Inventario "force deleted" event.
     */
    public function forceDeleted(Inventario $inventario): void
    {
        //
    }
}
