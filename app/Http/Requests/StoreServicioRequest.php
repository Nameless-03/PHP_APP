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
            'id_categoria' => ['required', 'exists:categorias,id'],
        ];
    }
}
