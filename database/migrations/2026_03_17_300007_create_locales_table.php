<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('locales', function (Blueprint $table) {
            $table->id();
            $table->string('loca_nombre', 100);
            $table->string('loca_direccion', 200)->nullable();
            $table->unsignedBigInteger('comu_id');

            $table->index('comu_id', 'fk_locales_comuna1_idx');
            $table->foreign('comu_id', 'fk_locales_comuna1')
                ->references('id')->on('comuna');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('locales');
    }
};
