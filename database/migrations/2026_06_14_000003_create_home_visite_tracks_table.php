<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('home_visite_tracks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('home_visite_id')->constrained('home_visite')->cascadeOnDelete();
            $table->decimal('latitude', 10, 7);
            $table->decimal('longitude', 10, 7);
            $table->float('accuracy')->nullable();
            $table->float('speed')->nullable();
            $table->tinyInteger('battery_level')->unsigned()->nullable();
            $table->timestamp('recorded_at');
            $table->timestamp('created_at')->useCurrent();

            $table->index(['home_visite_id', 'recorded_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('home_visite_tracks');
    }
};
