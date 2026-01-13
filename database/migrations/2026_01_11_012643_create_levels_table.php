<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('levels', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // 6e, 5e, 4e, 3e, Seconde, Première, Terminale
            $table->integer('order'); // 1, 2, 3... pour trier
            $table->enum('cycle', ['college', 'lycee']); // Collège ou Lycée
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('levels');
    }
};
