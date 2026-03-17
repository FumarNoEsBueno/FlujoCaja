<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('usuarios_por_caja', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('usua_id');
            $table->unsignedBigInteger('caja_id');
            $table->boolean('usca_habilitado');
            $table->date('usca_fecha_inicio')->nullable();

            $table->index('caja_id', 'fk_usuarios_has_cajas_cajas1_idx');
            $table->index('usua_id', 'fk_usuarios_has_cajas_usuarios1_idx');

            $table->foreign('usua_id', 'fk_usuarios_has_cajas_usuarios1')
                ->references('id')->on('usuarios');
            $table->foreign('caja_id', 'fk_usuarios_has_cajas_cajas1')
                ->references('id')->on('cajas');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('usuarios_por_caja');
    }
};
