<?php

namespace App\Http\Requests\Admin;

use App\Concerns\ProfileValidationRules;
use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;
use Illuminate\Validation\Rules\Password;

class UpdateUserRequest extends FormRequest
{
    use ProfileValidationRules;

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $user = $this->route('user');

        abort_unless($user instanceof User, 404);

        return [
            ...$this->profileRules($user->id),
            // Only set when the admin wants to reset it - left blank keeps
            // the current password.
            'password' => ['nullable', 'string', Password::default(), 'confirmed'],
            'role' => ['required', new Enum(UserRole::class)],
        ];
    }
}
