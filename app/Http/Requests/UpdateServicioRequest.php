<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Enums\ModalidadEnum;

class UpdateServicioRequest extends FormRequest
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
            'nombre' => ['sometimes', 'string', 'max:255'],
            'descripcion' => ['nullable', 'string'],
            'precio' => ['sometimes', 'numeric', 'min:0'],
            'modalidad' => ['sometimes', Rule::enum(ModalidadEnum::class)],
            'duracion' => ['sometimes', 'integer', 'min:1'],
            'ubicacion' => ['nullable', 'string', 'max:255'],
            'activo' => ['sometimes', 'boolean'],
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
