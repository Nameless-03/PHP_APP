<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePaqueteRequest extends FormRequest
{
    /**
     * Determina si el usuario está autorizado para realizar esta solicitud.
     */
    public function authorize(): bool
    {
        return $this->user() && $this->user()->esProfesional();
    }

    /**
     * Obtiene las reglas de validación que se aplican a la solicitud.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'nombre' => [
                'required',
                'string',
                'max:255',
                'regex:/[a-zA-ZñÑáéíóúüÁÉÍÓÚÜ]/',
                \Illuminate\Validation\Rule::unique('paquetes', 'nombre')->where(function ($query) {
                    return $query->where('id_profesional', $this->user()->id);
                })
            ],
            'descripcion' => ['nullable', 'string'],
            'descuento' => ['required', 'numeric', 'min:0'],
            'vencimiento' => ['nullable', 'integer', 'min:1'],
            'servicios' => [
                'required',
                'array',
                'min:1',
                function ($attribute, $value, $fail) {
                    foreach ($value as $index => $item) {
                        if (!is_array($item) || !isset($item['id'])) {
                            $fail("El formato del servicio en la posición {$index} no es válido.");
                            return;
                        }
                        $esPropio = \DB::table('servicios')
                            ->where('id', $item['id'])
                            ->where('id_profesional', $this->user()->id)
                            ->exists();
                        if (!$esPropio) {
                            $fail("Uno o más servicios seleccionados no pertenecen a tu perfil.");
                            return;
                        }
                    }
                }
            ],
            'servicios.*.id' => ['required', 'integer', 'exists:servicios,id'],
            'servicios.*.cantidad_sesiones' => ['required', 'integer', 'min:1'],
        ];
    }

    /**
     * Obtiene los mensajes personalizados para los errores de validación.
     */
    public function messages(): array
    {
        return [
            'nombre.required' => 'El nombre del paquete es obligatorio.',
            'descuento.required' => 'El descuento es obligatorio.',
            'descuento.min' => 'El descuento no puede ser menor a 0.',
            'vencimiento.min' => 'La duración de vencimiento debe ser al menos 1 día.',
            'servicios.required' => 'Debes asociar al menos un servicio al paquete.',
            'servicios.min' => 'Debes asociar al menos un servicio al paquete.',
            'servicios.*.cantidad_sesiones.required' => 'La cantidad de sesiones es obligatoria para cada servicio.',
            'servicios.*.cantidad_sesiones.min' => 'Cada servicio debe incluir al menos 1 sesión.',
        ];
    }
}
