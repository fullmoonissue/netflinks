<?php

declare(strict_types=1);

namespace App\Infrastructure\Console;

use App\Infrastructure\Database\DatabaseConstructor;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'db:create', description: 'Create the sqlite file as doctrine:database:create won\'t')]
class DbCreateCommand extends Command
{
    public function __construct(
        private readonly DatabaseConstructor $databaseConstructor,
        private readonly string $dbPath,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->databaseConstructor->construct($this->dbPath);

        return Command::SUCCESS;
    }
}
