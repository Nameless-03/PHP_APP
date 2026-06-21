<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Enums\DiaSemanaEnum;

class StoreDisponibilidadRequest extends FormRequest
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
            'dia_semana' => ['required', Rule::enum(DiaSemanaEnum::class)],
            'hora_inicio' => ['required', 'date_format:H:i'],
            'hora_fin' => ['required', 'date_format:H:i', 'after:hora_inicio'],
            'pausa_inicio' => ['nullable', 'date_format:H:i'],
            'pausa_minutos' => ['nullable', 'integer', 'min:0'],
            'buffer_minutos' => ['nullable', 'integer', 'min:0'],
        ];
    }
}
