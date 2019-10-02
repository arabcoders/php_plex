<?php declare(strict_types=1, ticks=1);
/*
 * This file is part of the PHPPlex package.
 *
 * (c) Abdulmohsen A. (admin@arabcoders.rog)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Symfony\Component\Console\Output\ConsoleOutput;

require __DIR__ . '/vendor/autoload.php';

$output = new ConsoleOutput();

if (PHP_SAPI !== 'cli') {
    $output->writeln('This script has to run under PHP-CLI.');
    exit(1);
}

if (PHP_VERSION_ID < 70100) {
    $output->writeln('PHP need to be v7.1 or higher.');
    exit(1);
}

if (!extension_loaded('curl')) {
    $output->writeln('You must enable PHP curl extension, otherwise we would not be able to get info from Plex.');
    exit(1);
}

if (!extension_loaded('xml')) {
    $output->writeln('You must enable PHP XML extension, otherwise we would not be able to read Plex API output.');
    exit(1);
}

if (!function_exists('proc_open')) {
    $output->writeln('proc_open() function is disabled. and it\'s required to send the scanner command.');
}

try {


    $app = new \phpplex\PHPPlex((array)require __DIR__ . '/config.php');

    $app->run();
} catch (Throwable $e) {

    if (!($e instanceof Exception)) {
        $e = new ErrorException($e->getMessage(), $e->getCode(), E_ERROR, $e->getFile(), $e->getLine());
    }

    $app->renderException($e, $output);
}