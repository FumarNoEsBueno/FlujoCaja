<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('movimientos', function (Blueprint $table) {
            $table->id();
            $table->string('movi_descripcion', 500)->nullable();
            $table->date('movi_fecha_ingreso');
            $table->string('movi_id_transaccion', 45);
            $table->string('movi_monto_total', 45);
            $table->string('movi_medio_pago', 45)->nullable();
            $table->integer('movi_propina')->nullable();
            $table->unsignedBigInteger('timo_id');
            $table->unsignedBigInteger('usua_id');
            $table->unsignedBigInteger('caja_id');

            $table->index('timo_id', 'fk_movimientos_tipo_movimiento1_idx');
            $table->index('usua_id', 'fk_movimientos_usuarios1_idx');
            $table->index('caja_id', 'fk_movimientos_cajas1_idx');

            $table->foreign('timo_id', 'fk_movimientos_tipo_movimiento1')
                ->references('id')->on('tipo_movimiento');
            $table->foreign('usua_id', 'fk_movimientos_usuarios1')
                ->references('id')->on('usuarios');
            $table->foreign('caja_id', 'fk_movimientos_cajas1')
                ->references('id')->on('cajas');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('movimientos');
    }
};
