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
        Schema::table('profesionales', function (Blueprint $table) {
            $table->string('foto_perfil')->nullable()->after('modalidad_preferida');
        });

        // Asegurar que el enlace simbólico del almacenamiento público esté creado
        if (!file_exists(public_path('storage')) && !is_link(public_path('storage'))) {
            try {
                \Illuminate\Support\Facades\Artisan::call('storage:link');
            } catch (\Exception $e) {
                // Ignorar si hay problemas de permisos en entornos de desarrollo restrictivos
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('profesionales', function (Blueprint $table) {
            $table->dropColumn('foto_perfil');
        });
    }
};
