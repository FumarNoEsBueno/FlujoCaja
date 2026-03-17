<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('usuarios', function (Blueprint $table) {
            $table->id();
            $table->string('usua_nombre', 45);
            $table->string('usua_apellido_p', 45);
            $table->string('usua_apellido_m', 45)->nullable();
            $table->string('usua_rut', 45);
            $table->string('usua_dv', 45);
            $table->string('usua_correo', 45)->nullable();
            $table->date('usua_fecha_nac');
            $table->string('usua_password', 255);
            $table->unsignedBigInteger('role_id');

            $table->index('role_id', 'fk_usuarios_roles1_idx');
            $table->foreign('role_id', 'fk_usuarios_roles1')
                ->references('id')->on('roles');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('usuarios');
    }
};
