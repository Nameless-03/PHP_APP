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
        // Add columns to paquetes
        Schema::table('paquetes', function (Blueprint $table) {
            if (!Schema::hasColumn('paquetes', 'descuento')) {
                $table->decimal('descuento', 10, 2)->default(0.00)->after('precio');
            }
        });

        // Add cantidad_sesiones to paquete_servicio pivot
        Schema::table('paquete_servicio', function (Blueprint $table) {
            if (!Schema::hasColumn('paquete_servicio', 'cantidad_sesiones')) {
                $table->integer('cantidad_sesiones')->default(1);
            }
        });

        // Create table to track sessions per service for a purchase
        if (!Schema::hasTable('compra_paquete_servicio')) {
            Schema::create('compra_paquete_servicio', function (Blueprint $table) {
                $table->foreignId('id_compra_paquete')->constrained('compras_paquete')->onDelete('cascade');
                $table->foreignId('id_servicio')->constrained('servicios')->onDelete('cascade');
                $table->integer('sesiones_disponibles')->default(0);
                $table->integer('sesiones_totales')->default(0);
                $table->primary(['id_compra_paquete', 'id_servicio']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('compra_paquete_servicio');

        Schema::table('paquete_servicio', function (Blueprint $table) {
            if (Schema::hasColumn('paquete_servicio', 'cantidad_sesiones')) {
                $table->dropColumn('cantidad_sesiones');
            }
        });

        Schema::table('paquetes', function (Blueprint $table) {
            if (Schema::hasColumn('paquetes', 'descuento')) {
                $table->dropColumn('descuento');
            }
        });
    }
};
