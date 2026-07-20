<?php

namespace App\Http\Requests\Accounting;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Validation des montants soumis depuis le modal d'édition du tableau
 * croisé Niveau × Type de frais (une valeur par type de frais, clé =
 * fee_type_id). Valeur vide/nulle = frais désactivé pour ce niveau.
 */
class UpdateLevelFeeAmountsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->can('parametrage.frais');
    }

    public function rules(): array
    {
        return [
            'level_id' => ['required', 'integer', 'exists:levels,id'],
            'amounts' => ['required', 'array'],
            'amounts.*' => ['nullable', 'numeric', 'min:0'],
        ];
    }
}
