<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Enums\ModalidadEnum;

class StoreServicioRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Require professional role (basic check, could also be handled by middleware)
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
            'precio' => ['required', 'numeric', 'min:0'],
            'modalidad' => ['required', Rule::enum(ModalidadEnum::class)],
            'duracion' => ['required', 'integer', 'min:1'], // in minutes
            'ubicacion' => ['nullable', 'string', 'max:255'],
            'activo' => ['boolean'],
            'id_categoria' => ['required', function ($attribute, $value, $fail) {
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
