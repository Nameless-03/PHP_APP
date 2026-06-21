<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Enums\MetodoPagoEnum;

class StorePagoRequest extends FormRequest
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
            'id_reserva' => ['required_without:id_compra', 'exists:reservas,id'],
            'id_compra' => ['required_without:id_reserva', 'exists:compras_paquete,id'],
            'monto' => ['required', 'numeric', 'min:0'],
            'metodo' => ['required', Rule::enum(MetodoPagoEnum::class)],
            'simular_error' => ['nullable', 'boolean'],
        ];
    }
}
