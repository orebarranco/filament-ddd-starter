<?php

declare(strict_types=1);

/**
 * @return array<int, string>
 */
function composerScript(string $name): array
{
    $composer = json_decode(
        (string) file_get_contents(base_path('composer.json')),
        associative: true,
        flags: JSON_THROW_ON_ERROR,
    );

    return data_get($composer, "scripts.{$name}", []);
}

/**
 * @param  array<int, string>  $scripts
 */
function scriptPositionOf(array $scripts, string $needle): int|false
{
    return array_search($needle, $scripts, strict: true);
}

it('seeds the database when a new project is created', function (): void {
    expect(composerScript('post-create-project-cmd'))
        ->toContain('@php artisan db:seed --force --ansi');
});

it('seeds only after the migrations have run when a new project is created', function (): void {
    $scripts = composerScript('post-create-project-cmd');

    expect(scriptPositionOf($scripts, '@php artisan db:seed --force --ansi'))
        ->toBeGreaterThan(scriptPositionOf($scripts, '@php artisan migrate --graceful --ansi'));
});

it('seeds the database when the repository is set up from a clone', function (): void {
    expect(composerScript('setup'))->toContain('@php artisan db:seed --force');
});

it('seeds only after the migrations have run when set up from a clone', function (): void {
    $scripts = composerScript('setup');

    expect(scriptPositionOf($scripts, '@php artisan db:seed --force'))
        ->toBeGreaterThan(scriptPositionOf($scripts, '@php artisan migrate --force'));
});
