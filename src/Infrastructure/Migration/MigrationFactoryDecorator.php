<?php

declare(strict_types=1);

namespace App\Infrastructure\Migration;

use Doctrine\Migrations\AbstractMigration;
use Doctrine\Migrations\Version\MigrationFactory;
use Psr\Container\ContainerInterface;

readonly class MigrationFactoryDecorator implements MigrationFactory
{
    public function __construct(
        private MigrationFactory $migrationFactory,
        private ContainerInterface $container,
    ) {
    }

    public function createVersion(string $migrationClassName): AbstractMigration
    {
        $instance = $this->migrationFactory->createVersion($migrationClassName);

        if (method_exists($instance, 'setContainer')) {
            $instance->setContainer($this->container);
        }

        return $instance;
    }
}
