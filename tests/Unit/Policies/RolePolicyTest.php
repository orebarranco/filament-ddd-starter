<?php

declare(strict_types=1);

use App\Policies\RolePolicy;
use Illuminate\Support\Facades\Gate;
use Spatie\Permission\Models\Role;

it('resolves the RolePolicy for the vendor Role model instead of falling back to null', function (): void {
    expect(Gate::getPolicyFor(Role::class))->toBeInstanceOf(RolePolicy::class);
});
