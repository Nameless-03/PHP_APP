<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Force delete all soft-deleted users so that their emails are freed up
        // and database cascades are triggered for their relations.
        $softDeletedUsers = DB::table('usuarios')->whereNotNull('deleted_at')->get();
        foreach ($softDeletedUsers as $user) {
            DB::table('usuarios')->where('id', $user->id)->delete();
        }

        // 2. Clean up any orphaned reservations where:
        // - the client does not exist
        // - OR the service does not exist (or is soft-deleted)
        // - OR the professional does not exist or is soft-deleted
        DB::table('reservas')
            ->whereNotExists(function ($query) {
                $query->select(DB::raw(1))
                      ->from('clientes')
                      ->whereColumn('clientes.id_usuario', 'reservas.id_cliente');
            })
            ->orWhereNotExists(function ($query) {
                $query->select(DB::raw(1))
                      ->from('servicios')
                      ->whereColumn('servicios.id', 'reservas.id_servicio')
                      ->whereNull('servicios.deleted_at');
            })
            ->orWhereNotExists(function ($query) {
                $query->select(DB::raw(1))
                      ->from('servicios')
                      ->join('profesionales', 'servicios.id_profesional', '=', 'profesionales.id_usuario')
                      ->join('usuarios', 'profesionales.id_usuario', '=', 'usuarios.id')
                      ->whereColumn('servicios.id', 'reservas.id_servicio')
                      ->whereNull('usuarios.deleted_at');
            })
            ->delete();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No reverse operation since it's a data cleanup migration
    }
};
