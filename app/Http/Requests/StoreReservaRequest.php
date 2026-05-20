<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreReservaRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user() && $this->user()->esCliente();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'id_servicio' => ['required', 'exists:servicios,id'],
            'fecha_hora_inicio' => ['required', 'date', 'after:now'],
            // 'fecha_hora_fin' is calculated automatically based on the service's duration
            'observaciones' => ['nullable', 'string'],
            'id_compra_paquete' => ['nullable', 'exists:compras_paquete,id'],
        ];
    }
}
