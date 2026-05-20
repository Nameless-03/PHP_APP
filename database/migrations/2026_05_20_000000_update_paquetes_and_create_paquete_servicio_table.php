<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Modificar la tabla paquetes para agregar la columna descripcion
        if (Schema::hasTable('paquetes') && !Schema::hasColumn('paquetes', 'descripcion')) {
            Schema::table('paquetes', function (Blueprint $table) {
                $table->text('descripcion')->nullable()->after('nombre');
            });
        }

        // 2. Crear la tabla pivot paquete_servicio
        if (!Schema::hasTable('paquete_servicio')) {
            Schema::create('paquete_servicio', function (Blueprint $table) {
                $table->foreignId('id_paquete')->constrained('paquetes')->onDelete('cascade');
                $table->foreignId('id_servicio')->constrained('servicios')->onDelete('cascade');
                $table->primary(['id_paquete', 'id_servicio']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('paquete_servicio');

        if (Schema::hasTable('paquetes') && Schema::hasColumn('paquetes', 'descripcion')) {
            Schema::table('paquetes', function (Blueprint $table) {
                $table->dropColumn('descripcion');
            });
        }
    }
};
