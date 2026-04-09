<?php

declare(strict_types=1);

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use Domain\Identity\Actions\CreateUserAction;
use Domain\Identity\DataTransferObjects\UserData;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

final class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        return resolve(CreateUserAction::class)->execute(UserData::fromArray($data));
    }
}
