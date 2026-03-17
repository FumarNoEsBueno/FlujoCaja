<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('comuna', function (Blueprint $table) {
            $table->id();
            $table->string('comu_nombre', 45);
            $table->unsignedBigInteger('regi_id');

            $table->index('regi_id', 'fk_comuna_region1_idx');
            $table->foreign('regi_id', 'fk_comuna_region1')
                ->references('id')->on('region');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('comuna');
    }
};
