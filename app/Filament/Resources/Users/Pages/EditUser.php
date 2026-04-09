<?php

declare(strict_types=1);

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use Domain\Identity\Actions\DeleteUserAction;
use Domain\Identity\Actions\UpdateUserAction;
use Domain\Identity\DataTransferObjects\UserData;
use Domain\Identity\Models\User;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

final class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->action(fn (User $record) => resolve(DeleteUserAction::class)->execute($record)),
        ];
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        /** @var User $record */
        return resolve(UpdateUserAction::class)->execute($record, UserData::fromArray($data));
    }
}
