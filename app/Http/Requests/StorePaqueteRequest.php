<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePaqueteRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user() && $this->user()->esProfesional();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'nombre' => ['required', 'string', 'max:255'],
            'descripcion' => ['nullable', 'string'],
            'cantidad_sesiones' => ['required', 'integer', 'min:1'],
            'precio' => ['required', 'numeric', 'min:0'],
            'vencimiento' => ['nullable', 'integer', 'min:1'],
            'servicios' => ['required', 'array', 'min:1'],
            'servicios.*' => [
                'required',
                'integer',
                'exists:servicios,id',
                function ($attribute, $value, $fail) {
                    $esPropio = \DB::table('servicios')
                        ->where('id', $value)
                        ->where('id_profesional', $this->user()->id)
                        ->exists();
                    if (!$esPropio) {
                        $fail('Uno o más servicios seleccionados no pertenecen a tu perfil.');
                    }
                }
            ],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'nombre.required' => 'El nombre del paquete es obligatorio.',
            'cantidad_sesiones.required' => 'La cantidad de sesiones es obligatoria.',
            'cantidad_sesiones.min' => 'El paquete debe incluir al menos 1 sesión.',
            'precio.required' => 'El precio del paquete es obligatorio.',
            'precio.min' => 'El precio del paquete no puede ser menor a 0.',
            'vencimiento.min' => 'La duración de vencimiento debe ser al menos 1 día.',
            'servicios.required' => 'Debes asociar al menos un servicio al paquete.',
            'servicios.min' => 'Debes asociar al menos un servicio al paquete.',
        ];
    }
}
