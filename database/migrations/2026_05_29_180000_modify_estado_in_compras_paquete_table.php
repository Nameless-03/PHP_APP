<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Alter enum column to string (VARCHAR) to support 'pendiente' state
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE compras_paquete MODIFY estado VARCHAR(255) DEFAULT 'pendiente'");
        } elseif (DB::getDriverName() === 'pgsql') {
            DB::statement("ALTER TABLE compras_paquete ALTER COLUMN estado DROP DEFAULT;");
            DB::statement("ALTER TABLE compras_paquete ALTER COLUMN estado TYPE VARCHAR(255) USING estado::varchar;");
            DB::statement("ALTER TABLE compras_paquete ALTER COLUMN estado SET DEFAULT 'pendiente';");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Restore back to original enum type and default 'activo'
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE compras_paquete MODIFY estado ENUM('activo', 'agotado', 'vencido') DEFAULT 'activo'");
        } elseif (DB::getDriverName() === 'pgsql') {
            DB::statement("ALTER TABLE compras_paquete ALTER COLUMN estado DROP DEFAULT;");
            DB::statement("ALTER TABLE compras_paquete ALTER COLUMN estado TYPE compras_paquete_estado_enum USING estado::compras_paquete_estado_enum;");
            DB::statement("ALTER TABLE compras_paquete ALTER COLUMN estado SET DEFAULT 'activo';");
        }
    }
};
