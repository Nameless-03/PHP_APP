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
        // Drop the enum check constraint in PostgreSQL to allow 'pendiente' status
        DB::statement("ALTER TABLE compras_paquete DROP CONSTRAINT IF EXISTS compras_paquete_estado_check;");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Restore check constraint if needed
        DB::statement("ALTER TABLE compras_paquete ADD CONSTRAINT compras_paquete_estado_check CHECK (estado::text = ANY (ARRAY['activo'::charactervarying, 'agotado'::charactervarying, 'vencido'::charactervarying]::text[]));");
    }
};
