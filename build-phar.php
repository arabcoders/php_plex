<?php declare(strict_types=1);
/*
 * This file is part of the PHPPlex package.
 *
 * (c) Abdulmohsen A. (admin@arabcoders.rog)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

if ('1' === ini_get('phar.readonly')) {
    echo 'Unable to build, phar.readonly in php.ini is set to read only.' . PHP_EOL;
    echo 'try using php -dphar.readonly=0 ' . basename(__FILE__) . PHP_EOL;
    exit(1);
}

define('BASE_SRC', realpath(dirname(__DIR__) . DIRECTORY_SEPARATOR . 'app') . DIRECTORY_SEPARATOR);
define('BUILD_DIR', realpath(dirname(__DIR__) . DIRECTORY_SEPARATOR . 'build') . DIRECTORY_SEPARATOR);

const PHAR_NAME = 'PHPPlex.phar';
const ENTRY_NAME = 'PHPPlex.php';

if (file_exists(BUILD_DIR . PHAR_NAME)) {
    unlink(BUILD_DIR . PHAR_NAME);
}

// create phar
$phar = new Phar(BUILD_DIR . PHAR_NAME);
$phar->setSignatureAlgorithm(Phar::SHA1);

// start buffering. Mandatory to modify stub to add shebang
$phar->startBuffering();

// Create the default stub from the main entry point.
$defaultStub = $phar::createDefaultStub(ENTRY_NAME);

// Add the rest of the apps files
$phar->buildFromDirectory(BASE_SRC);

// Customize the stub to add the shebang
$stub = "#!/usr/bin/env php \n" . $defaultStub;

// Add the stub
$phar->setStub($stub);

$phar->stopBuffering();

// plus - compressing it into gzip
$phar->compressFiles(Phar::GZ);

# Make the file executable
chmod(realpath(BUILD_DIR . PHAR_NAME), 0770);

echo PHAR_NAME . ' successfully created' . PHP_EOL;