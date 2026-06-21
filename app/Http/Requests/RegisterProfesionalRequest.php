<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Enums\ModalidadEnum;

class RegisterProfesionalRequest extends FormRequest
{
    /**
     * Determina si el usuario está autorizado para realizar esta solicitud.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Obtiene las reglas de validación que se aplican a la solicitud.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'nombre' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:usuarios'],
            'password' => [$this->has('google_id') ? 'nullable' : 'required', 'string', 'min:8', 'confirmed'],
            'descripcion' => ['nullable', 'string'],
            'experiencia' => ['nullable', 'string'],
            'ubicacion' => ['nullable', 'string', 'max:255'],
            'modalidad_preferida' => ['nullable', Rule::enum(ModalidadEnum::class)],
            'foto_perfil' => ['nullable', 'string'],
            'google_id' => ['nullable', 'string'],
        ];
    }
}
