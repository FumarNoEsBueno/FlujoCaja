<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('productos_del_movimiento', function (Blueprint $table) {
            $table->id();
            $table->integer('pdmo_cantidad');
            $table->integer('pdmo_monto_unitario');
            $table->unsignedBigInteger('movi_id');
            $table->unsignedBigInteger('prod_id');

            $table->index('prod_id', 'fk_movimientos_has_producto_producto1_idx');
            $table->index('movi_id', 'fk_movimientos_has_producto_movimientos1_idx');

            $table->foreign('movi_id', 'fk_movimientos_has_producto_movimientos1')
                ->references('id')->on('movimientos');
            $table->foreign('prod_id', 'fk_movimientos_has_producto_producto1')
                ->references('id')->on('producto');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('productos_del_movimiento');
    }
};
