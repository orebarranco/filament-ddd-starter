<?php

declare(strict_types=1);

namespace App\Console\Commands\Domain;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;

final class MakeDomainCommand extends Command
{
    protected $signature = 'domain:make {name : The name of the domain (e.g. Invoices, Customers)}';

    protected $description = 'Create a new domain directory structure in src/Domain/';

    public function __construct(private readonly Filesystem $files)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $domain = mb_trim($this->argument('name'));

        $basePath = base_path('src/Domain/'.$domain);

        if ($this->files->exists($basePath)) {
            $this->error(sprintf('Domain [%s] already exists.', $domain));

            return self::FAILURE;
        }

        $directories = [
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

        foreach ($directories as $directory) {
            $path = sprintf('%s/%s', $basePath, $directory);
            $this->files->makeDirectory($path, 0755, recursive: true);
            $this->files->put($path.'/.gitkeep', '');
        }

        $this->info(sprintf('Domain [%s] created successfully at [src/Domain/%s].', $domain, $domain));
        $this->line('');

        foreach ($directories as $index => $directory) {
            $isLast = $index === array_key_last($directories);
            $prefix = $isLast ? '└──' : '├──';
            $this->line(sprintf('  <fg=gray>%s</> %s', $prefix, $directory));
        }

        return self::SUCCESS;
    }
}
