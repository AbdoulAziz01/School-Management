<?php

// app/Http/Requests/UpdateParentContactRequest.php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Validation des informations de contact du parent / tuteur.
 * Utilisable dans n'importe quel contrôleur qui met à jour ces champs sur un élève.
 *
 * Exemple d'utilisation dans un contrôleur :
 *
 *   public function updateParent(UpdateParentContactRequest $request, User $student): RedirectResponse
 *   {
 *       $student->update($request->validated());
 *       return back()->with('success', 'Contact parent mis à jour.');
 *   }
 */
final class UpdateParentContactRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Ajuster selon votre logique d'autorisation (admin, enseignant, etc.)
        return true;
    }

    public function rules(): array
    {
        return [
            'parent_name' => ['nullable', 'string', 'max:150'],

            /*
             * Format WhatsApp international : +<indicatif><numéro>
             * Exemples valides : +221771234567  +33612345678  +12125551234
             * Règle : commence par +, indicatif 1–3 chiffres, numéro 6–12 chiffres
             * Total : 8 à 16 caractères (hors +)
             */
            'parent_whatsapp' => [
                'nullable',
                'string',
                'regex:/^\+[1-9]\d{1,2}[0-9]{6,12}$/',
                'max:20',
            ],

            'parent_lang' => [
                'nullable',
                Rule::in(User::PARENT_LANGS),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'parent_whatsapp.regex' => 'Le numéro WhatsApp doit être au format international : +221771234567',
        ];
    }

    public function attributes(): array
    {
        return [
            'parent_name'     => 'nom du parent',
            'parent_whatsapp' => 'numéro WhatsApp',
            'parent_lang'     => 'langue de communication',
        ];
    }
}
