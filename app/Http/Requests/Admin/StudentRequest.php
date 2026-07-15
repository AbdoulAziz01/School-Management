<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Règles de validation partagées par StudentController::store() et update(),
 * qui étaient dupliquées à l'identique (à l'exception de l'unicité de
 * l'email, qui s'auto-exclut le cas échéant via la route "student").
 */
class StudentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $student = $this->route('student');

        return [
            'first_name'        => 'required|string|max:255',
            'last_name'         => 'required|string|max:255',
            'email'             => ['nullable', 'string', 'email', 'max:255', Rule::unique('users')->ignore($student?->id)],
            'date_of_birth'     => 'required|date|before:today',
            'class_id'          => 'nullable|exists:classes,id',
            'status'            => ['required', 'string', Rule::in(['pending', 'approved', 'rejected'])],
            'photo'             => ['nullable', 'image', 'max:4096'],
            'birth_certificate' => ['nullable', 'file', 'mimes:pdf', 'max:8192'],
            'parent_name'       => 'nullable|string|max:150',
            'parent_whatsapp'   => 'nullable|string|max:20',
            'parent_lang'       => ['nullable', Rule::in(['fr_text', 'wo_audio', 'pu_audio'])],
        ];
    }
}
