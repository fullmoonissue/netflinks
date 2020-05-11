<?php

// Bootstrap file used only to fix "Test code or tested code did not remove its own exception handlers" after IT
// cf https://github.com/symfony/symfony/issues/53812#issuecomment-1962740145

declare(strict_types=1);

use Symfony\Component\ErrorHandler\ErrorHandler;

require dirname(__DIR__).'/vendor/autoload.php';

set_exception_handler([new ErrorHandler(), 'handleException']);
