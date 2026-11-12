<?php

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;

use function Laravel\Prompts\password;
use function Laravel\Prompts\text;

Artisan::command('backoffice:create-admin', function () {
    $data = [
        'name' => text('Name', required: true),
        'email' => text('E-Mail', required: true),
        'password' => password('Passwort (mind. 12 Zeichen, Groß-/Kleinbuchstaben, Ziffern)', required: true),
    ];

    $validator = Validator::make($data, [
        'email' => ['email', 'unique:users,email'],
        'password' => [Password::min(12)->mixedCase()->numbers()],
    ]);

    if ($validator->fails()) {
        foreach ($validator->errors()->all() as $error) {
            $this->error($error);
        }

        return 1;
    }

    User::query()->create([...$data, 'role' => UserRole::Admin]);

    $this->info("Admin {$data['email']} angelegt. Die Zwei-Faktor-Authentifizierung wird bei der ersten Anmeldung eingerichtet.");
})->purpose('Admin-Zugang für das Backoffice anlegen');
