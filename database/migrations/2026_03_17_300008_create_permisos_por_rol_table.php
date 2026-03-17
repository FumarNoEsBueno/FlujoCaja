<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('permisos_por_rol', function (Blueprint $table) {
            $table->unsignedBigInteger('perm_id');
            $table->unsignedBigInteger('role_id');
            $table->boolean('pero_activo');

            $table->primary(['perm_id', 'role_id']);
            $table->index('role_id', 'fk_permisos_has_roles_roles1_idx');
            $table->index('perm_id', 'fk_permisos_has_roles_permisos_idx');

            $table->foreign('perm_id', 'fk_permisos_has_roles_permisos')
                ->references('id')->on('permisos');
            $table->foreign('role_id', 'fk_permisos_has_roles_roles1')
                ->references('id')->on('roles');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('permisos_por_rol');
    }
};
