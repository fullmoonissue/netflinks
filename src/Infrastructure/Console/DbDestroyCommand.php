<?php

declare(strict_types=1);

namespace App\Infrastructure\Console;

use App\Infrastructure\Database\DatabaseDestructor;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'db:destroy', description: 'Delete the sqlite file')]
class DbDestroyCommand extends Command
{
    public function __construct(
        private readonly DatabaseDestructor $databaseDestructor,
        private readonly string $dbPath,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->databaseDestructor->destruct($this->dbPath);

        return Command::SUCCESS;
    }
}
