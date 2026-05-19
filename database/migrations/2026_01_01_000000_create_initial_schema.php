<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Usuarios
        Schema::create('usuarios', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->string('email')->unique();
            $table->string('password');
            $table->timestamp('fecha_registro')->useCurrent();
            $table->enum('role', ['cliente', 'profesional', 'admin']);
            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();
        });

        // 2. Clientes
        Schema::create('clientes', function (Blueprint $table) {
            $table->foreignId('id_usuario')->primary()->constrained('usuarios')->onDelete('cascade');
            $table->string('telefono')->nullable();
            $table->string('foto_perfil')->nullable();
            $table->timestamps();
        });

        // 3. Profesionales
        Schema::create('profesionales', function (Blueprint $table) {
            $table->foreignId('id_usuario')->primary()->constrained('usuarios')->onDelete('cascade');
            $table->text('descripcion')->nullable();
            $table->text('experiencia')->nullable();
            $table->string('ubicacion')->nullable();
            $table->enum('modalidad_preferida', ['presencial', 'remota', 'hibrida'])->default('presencial');
            $table->decimal('reputacion', 3, 2)->default(0.0);
            $table->timestamps();
        });

        // 4. Admins
        Schema::create('admins', function (Blueprint $table) {
            $table->foreignId('id_usuario')->primary()->constrained('usuarios')->onDelete('cascade');
            $table->timestamps();
        });

        // 5. Categorias
        Schema::create('categorias', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->text('descripcion')->nullable();
            $table->timestamps();
        });

        // 6. Servicios
        Schema::create('servicios', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->text('descripcion')->nullable();
            $table->decimal('precio', 10, 2);
            $table->enum('modalidad', ['presencial', 'remota', 'hibrida']);
            $table->integer('duracion')->comment('en minutos');
            $table->string('ubicacion')->nullable();
            $table->boolean('activo')->default(true);
            $table->foreignId('id_profesional')->constrained('profesionales', 'id_usuario')->onDelete('cascade');
            $table->foreignId('id_categoria')->constrained('categorias')->onDelete('cascade');
            $table->timestamps();
            $table->softDeletes();
        });

        // 7. Disponibilidad
        Schema::create('disponibilidades', function (Blueprint $table) {
            $table->id();
            $table->enum('dia_semana', ['lunes', 'martes', 'miercoles', 'jueves', 'viernes', 'sabado', 'domingo']);
            $table->time('hora_inicio');
            $table->time('hora_fin');
            $table->integer('pausa_minutos')->default(0);
            $table->integer('buffer_minutos')->default(0);
            $table->foreignId('id_profesional')->constrained('profesionales', 'id_usuario')->onDelete('cascade');
            $table->timestamps();
        });

        // 8. Excepciones Agenda
        Schema::create('excepciones_agenda', function (Blueprint $table) {
            $table->id();
            $table->date('fecha');
            $table->string('motivo')->nullable();
            $table->boolean('disponible');
            $table->foreignId('id_profesional')->constrained('profesionales', 'id_usuario')->onDelete('cascade');
            $table->timestamps();
        });

        // 9. Reservas
        Schema::create('reservas', function (Blueprint $table) {
            $table->id();
            $table->dateTime('fecha_hora_inicio');
            $table->dateTime('fecha_hora_fin');
            $table->enum('estado', ['pendiente', 'confirmada', 'pagada', 'en_curso', 'finalizada', 'cancelada', 'no_asistida'])->default('pendiente');
            $table->text('observaciones')->nullable();
            $table->foreignId('id_cliente')->constrained('clientes', 'id_usuario')->onDelete('cascade');
            $table->foreignId('id_servicio')->constrained('servicios')->onDelete('cascade');
            $table->timestamps();
            $table->softDeletes();
        });

        // 10. Paquetes
        Schema::create('paquetes', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->integer('cantidad_sesiones');
            $table->decimal('precio', 10, 2);
            $table->integer('vencimiento')->nullable()->comment('Días de validez desde la compra');
            $table->foreignId('id_profesional')->constrained('profesionales', 'id_usuario')->onDelete('cascade');
            $table->timestamps();
        });

        // 11. Compra Paquetes
        Schema::create('compras_paquete', function (Blueprint $table) {
            $table->id();
            $table->integer('sesiones_disponibles');
            $table->dateTime('fecha_compra')->useCurrent();
            $table->enum('estado', ['activo', 'agotado', 'vencido'])->default('activo');
            $table->foreignId('id_cliente')->constrained('clientes', 'id_usuario')->onDelete('cascade');
            $table->foreignId('id_paquete')->constrained('paquetes')->onDelete('cascade');
            $table->timestamps();
        });

        // 12. Pagos
        Schema::create('pagos', function (Blueprint $table) {
            $table->id();
            $table->decimal('monto', 10, 2);
            $table->dateTime('fecha')->useCurrent();
            $table->enum('metodo', ['paypal', 'efectivo', 'transferencia', 'otro']);
            $table->enum('estado', ['pendiente', 'completado', 'fallido', 'reembolsado'])->default('pendiente');
            $table->string('referencia_externa')->nullable();
            $table->foreignId('id_reserva')->nullable()->constrained('reservas')->onDelete('cascade');
            $table->foreignId('id_compra')->nullable()->constrained('compras_paquete')->onDelete('cascade');
            $table->timestamps();
        });

        // 13. Videollamadas
        Schema::create('videollamadas', function (Blueprint $table) {
            $table->id();
            $table->string('enlace');
            $table->string('token');
            $table->dateTime('fecha_creacion')->useCurrent();
            $table->foreignId('id_reserva')->constrained('reservas')->onDelete('cascade');
            $table->timestamps();
        });

        // 14. Calificaciones
        Schema::create('calificaciones', function (Blueprint $table) {
            $table->id();
            $table->integer('puntuacion');
            $table->text('comentario')->nullable();
            $table->dateTime('fecha')->useCurrent();
            $table->foreignId('id_reserva')->constrained('reservas')->onDelete('cascade');
            $table->timestamps();
        });

        // 15. Notificaciones
        Schema::create('notificaciones', function (Blueprint $table) {
            $table->id();
            $table->string('titulo');
            $table->text('mensaje');
            $table->enum('tipo', ['confirmacion', 'recordatorio', 'cancelacion', 'modificacion', 'mensaje', 'otro']);
            $table->boolean('leida')->default(false);
            $table->dateTime('fecha')->useCurrent();
            $table->foreignId('id_usuario')->constrained('usuarios')->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notificaciones');
        Schema::dropIfExists('calificaciones');
        Schema::dropIfExists('videollamadas');
        Schema::dropIfExists('pagos');
        Schema::dropIfExists('compras_paquete');
        Schema::dropIfExists('paquetes');
        Schema::dropIfExists('reservas');
        Schema::dropIfExists('excepciones_agenda');
        Schema::dropIfExists('disponibilidades');
        Schema::dropIfExists('servicios');
        Schema::dropIfExists('categorias');
        Schema::dropIfExists('admins');
        Schema::dropIfExists('profesionales');
        Schema::dropIfExists('clientes');
        Schema::dropIfExists('usuarios');
    }
};
