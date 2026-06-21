<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Enums\ModalidadEnum;

class UpdateServicioRequest extends FormRequest
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
                'sometimes',
                'string',
                'max:50',
                'regex:/[a-zA-ZñÑáéíóúüÁÉÍÓÚÜ]/',
                Rule::unique('servicios', 'nombre')->where(function ($query) {
                    return $query->where('id_profesional', $this->user()->id)->whereNull('deleted_at');
                })->ignore($this->route('servicio'))
            ],
            'descripcion' => ['nullable', 'string'],
            'precio' => ['sometimes', 'numeric', 'min:0'],
            'modalidad' => ['sometimes', Rule::enum(ModalidadEnum::class)],
            'duracion' => ['sometimes', 'integer', 'min:1'],
            'ubicacion' => ['nullable', 'string', 'max:255'],
            'activo' => ['sometimes', 'boolean'],
            'limite_cancelacion_horas' => ['sometimes', 'integer', 'min:0'],
            'id_categoria' => ['sometimes', function ($attribute, $value, $fail) {
                if (is_numeric($value)) {
                    if (!\DB::table('categorias')->where('id', $value)->exists()) {
                        $fail('La categoría seleccionada no existe.');
                    }
                } else {
                    if (is_string($value)) {
                        if (empty(trim($value))) {
                            $fail('El nombre de la nueva categoría no puede estar vacío.');
                        }
                    } else {
                        $fail('Categoría inválida.');
                    }
                }
            }],
        ];
    }
}
