<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTaskRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'title' => ['sometimes', 'string', 'max:255'],
            'description' => ['sometimes', 'nullable', 'string'],
            'status' => ['sometimes', 'in:pending,in_progress,done'],
            'due_date' => ['sometimes', 'nullable', 'date'],
            'tags' => ['sometimes', 'array'],
            'tags.*' => ['integer', 'exists:tags,id'],
        ];
    }

    /**
     * Custom validation messages.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'title.string' => 'El título de la tarea debe ser un texto válido.',
            'title.max' => 'El título de la tarea no puede superar los 255 caracteres.',
            'description.string' => 'La descripción de la tarea debe ser un texto válido.',
            'status.in' => 'El estado enviado no es válido para actualizar la tarea. Valores permitidos: pending (pendiente), in_progress (en progreso) o done (completada). Ejemplo: "status": "in_progress".',
            'due_date.date' => 'La fecha límite debe tener un formato de fecha válido (por ejemplo: 2026-03-26 18:30:00).',
            'tags.array' => 'El campo etiquetas debe enviarse como una lista (array).',
            'tags.*.integer' => 'Cada etiqueta debe identificarse con un ID numérico entero.',
            'tags.*.exists' => 'Una o más etiquetas enviadas no existen en el sistema.',
        ];
    }

    /**
     * Custom attribute names.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'title' => 'título',
            'description' => 'descripción',
            'status' => 'estado',
            'due_date' => 'fecha límite',
            'tags' => 'etiquetas',
            'tags.*' => 'etiqueta',
        ];
    }
}
