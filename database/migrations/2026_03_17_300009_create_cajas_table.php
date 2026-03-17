<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cajas', function (Blueprint $table) {
            $table->id();
            $table->string('caja_nombre', 45);
            $table->unsignedBigInteger('loca_id');

            $table->index('loca_id', 'fk_cajas_locales1_idx');
            $table->foreign('loca_id', 'fk_cajas_locales1')
                ->references('id')->on('locales');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cajas');
    }
};
