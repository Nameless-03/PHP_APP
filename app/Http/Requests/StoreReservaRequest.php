<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreReservaRequest extends FormRequest
{
    /**
     * Determina si el usuario está autorizado para realizar esta solicitud.
     */
    public function authorize(): bool
    {
        return $this->user() && $this->user()->esCliente();
    }

    /**
     * Obtiene las reglas de validación que se aplican a la solicitud.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'id_servicio' => ['required', 'exists:servicios,id'],
            'fecha_hora_inicio' => ['required', 'date', 'after:now'],
            // 'fecha_hora_fin' se calcula automáticamente basándose en la duración del servicio
            'observaciones' => ['nullable', 'string'],
            'id_compra_paquete' => ['nullable', 'exists:compras_paquete,id'],
        ];
    }
}
