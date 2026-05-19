<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Enums\MetodoPagoEnum;

class StorePagoRequest extends FormRequest
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
            'id_reserva' => ['required_without:id_compra', 'exists:reservas,id'],
            'id_compra' => ['required_without:id_reserva', 'exists:compras_paquete,id'],
            'monto' => ['required', 'numeric', 'min:0'],
            'metodo' => ['required', Rule::enum(MetodoPagoEnum::class)],
        ];
    }
}
