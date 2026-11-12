<?php

namespace App\Filament\Resources\Users;

use App\Enums\UserRole;
use App\Filament\Resources\Users\Pages\ManageUsers;
use App\Models\User;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Validation\Rules\Password;
use UnitEnum;

/**
 * Team members. Users are deactivated rather than deleted, so the
 * audit log keeps pointing at a real person.
 */
class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $modelLabel = 'Teammitglied';

    protected static ?string $pluralModelLabel = 'Team';

    protected static ?string $navigationLabel = 'Team';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUsers;

    protected static string|UnitEnum|null $navigationGroup = 'Verwaltung';

    protected static ?int $navigationSort = 22;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                TextInput::make('email')
                    ->email()
                    ->required()
                    ->unique(ignoreRecord: true),
                TextInput::make('password')
                    ->password()
                    ->revealable()
                    ->rule(Password::min(12)->mixedCase()->numbers())
                    ->required(fn (string $operation): bool => $operation === 'create')
                    ->dehydrated(fn (?string $state): bool => filled($state))
                    ->helperText(fn (string $operation): ?string => $operation === 'edit' ? 'Leer lassen, um das aktuelle Passwort zu behalten.' : null),
                Select::make('role')
                    ->options(UserRole::class)
                    ->default(UserRole::Team)
                    ->required(),
                Toggle::make('is_active')
                    ->label('Aktiv')
                    ->helperText('Inaktive Nutzer:innen können sich nicht anmelden.')
                    ->default(true),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable(),
                TextColumn::make('email')
                    ->searchable(),
                TextColumn::make('role')
                    ->badge(),
                IconColumn::make('mfa')
                    ->label('2FA eingerichtet')
                    ->state(fn (User $record): bool => filled($record->app_authentication_secret))
                    ->boolean(),
                IconColumn::make('is_active')
                    ->label('Aktiv')
                    ->boolean(),
            ])
            ->recordActions([
                EditAction::make(),
                Action::make('resetMfa')
                    ->label('2FA zurücksetzen')
                    ->icon(Heroicon::OutlinedKey)
                    ->color('warning')
                    ->requiresConfirmation()
                    ->modalDescription('Die Person muss ihre Authenticator-App bei der nächsten Anmeldung neu einrichten.')
                    ->visible(fn (User $record): bool => filled($record->app_authentication_secret))
                    ->action(function (User $record): void {
                        $record->saveAppAuthenticationSecret(null);
                        $record->saveAppAuthenticationRecoveryCodes(null);
                    }),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageUsers::route('/'),
        ];
    }
}
