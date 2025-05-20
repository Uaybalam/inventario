<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('inventarios', function (Blueprint $table) {
            $table->id('id_inventario');
            $table->foreignId('id_producto_sku')->constrained('producto_skus', 'id_producto_sku');
            $table->integer('cantidad');
            $table->dateTime('fecha_actualizacion')->nullable();
            $table->timestamps();
            $table->unsignedBigInteger('id_responsable')->nullable();
            $table->foreign('id_responsable')
                  ->references('id_responsable')
                  ->on('responsables')
                  ->onDelete('set null');
            $table->unsignedBigInteger('id_ubicacion')->nullable();
            $table->foreign('id_ubicacion')
                  ->references('id_ubicacion')
                  ->on('ubicaciones')
                  ->onDelete('set null');
            $table->unsignedBigInteger('id_estado')->nullable();
            $table->foreign('id_estado')
                  ->references('id_estado')
                  ->on('estados')
                  ->onDelete('set null');
        });
        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventarios');
    }
};
