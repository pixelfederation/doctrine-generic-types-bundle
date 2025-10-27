<?php

declare(strict_types=1);

use Symfony\Component\Dotenv\Dotenv;
use Symfony\Component\ErrorHandler\ErrorHandler;
use Symfony\Component\Filesystem\Filesystem;

require dirname(__DIR__) . '/vendor/autoload.php';

set_exception_handler([new ErrorHandler(), 'handleException']);

(new Dotenv())->usePutenv()->bootEnv(dirname(__DIR__) . '/.env.test');
(new Filesystem())->remove(dirname(__DIR__) . '/tests/TestApplication/var/cache');

// phpcs:ignore SlevomatCodingStandard.Variables.DisallowSuperGlobalVariable.DisallowedSuperGlobalVariable
if ($_SERVER['APP_DEBUG']) {
    umask(0000);
}
