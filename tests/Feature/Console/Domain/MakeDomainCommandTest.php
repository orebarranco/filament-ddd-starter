<?php

declare(strict_types=1);

use Illuminate\Filesystem\Filesystem;

beforeEach(function (): void {
    $this->files = new Filesystem;
    $this->domainPath = base_path('src/Domain/TestDomain');
});

afterEach(function (): void {
    if ($this->files->exists($this->domainPath)) {
        $this->files->deleteDirectory($this->domainPath);
    }
});

it('creates all domain subdirectories', function (): void {
    $this->artisan('domain:make', ['name' => 'TestDomain'])
        ->assertSuccessful();

    $expectedDirectories = [
        'Actions',
        'QueryBuilders',
        'Collections',
        'DataTransferObjects',
        'Events',
        'Exceptions',
        'Listeners',
        'Models',
        'Rules',
        'States',
    ];

    foreach ($expectedDirectories as $directory) {
        expect($this->domainPath.'/'.$directory)->toBeDirectory();
    }
});

it('places a .gitkeep inside each subdirectory', function (): void {
    $this->artisan('domain:make', ['name' => 'TestDomain'])
        ->assertSuccessful();

    $expectedDirectories = [
        'Actions',
        'QueryBuilders',
        'Collections',
        'DataTransferObjects',
        'Events',
        'Exceptions',
        'Listeners',
        'Models',
        'Rules',
        'States',
    ];

    foreach ($expectedDirectories as $directory) {
        expect($this->domainPath.'/'.$directory.'/.gitkeep')->toBeFile();
    }
});

it('outputs a success message with the domain name', function (): void {
    $this->artisan('domain:make', ['name' => 'TestDomain'])
        ->expectsOutputToContain('Domain [TestDomain] created successfully at [src/Domain/TestDomain].')
        ->assertSuccessful();
});

it('fails when the domain already exists', function (): void {
    $this->files->makeDirectory($this->domainPath, 0755, recursive: true);

    $this->artisan('domain:make', ['name' => 'TestDomain'])
        ->expectsOutputToContain('Domain [TestDomain] already exists.')
        ->assertFailed();
});
