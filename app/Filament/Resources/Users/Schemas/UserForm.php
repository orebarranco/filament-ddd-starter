<?php

declare(strict_types=1);

namespace App\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

final class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Nombre')
                    ->required()
                    ->maxLength(255),
                TextInput::make('email')
                    ->label('Correo electrónico')
                    ->email()
                    ->required()
                    ->maxLength(255)
                    ->unique(ignoreRecord: true),
                TextInput::make('password')
                    ->label('Contraseña')
                    ->password()
                    ->revealable()
                    ->required(fn (string $operation): bool => $operation === 'create')
                    ->dehydrated(fn (?string $state): bool => filled($state))
                    ->helperText(fn (string $operation): string => $operation === 'edit'
                        ? 'Deja en blanco para mantener la contraseña actual.'
                        : '')
                    ->maxLength(255),
                Select::make('roles')
                    ->label('Roles')
                    ->multiple()
                    ->relationship(titleAttribute: 'name')
                    ->preload()
                    ->searchable()
                    ->helperText('Sin ningún rol asignado, el usuario no puede entrar al panel.'),
                Toggle::make('active')
                    ->label('Activo')
                    ->default(true)
                    ->helperText('Al desactivarlo se le niega el acceso al panel sin perder sus roles.'),
            ]);
    }
}
