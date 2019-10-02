<?php declare(strict_types=1);
/*
 * This file is part of the PHPPlex package.
 *
 * (c) Abdulmohsen A. (admin@arabcoders.rog)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace phpplex\Commands;

use phpplex\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Process\Process;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @author Abdulmohsen A. <admin@arabcoders.rog>
 */
class Scan extends Command
{
    protected $locations = [];

    protected function configure(): void
    {
        $this->setName('scan')
            ->setDescription('Run Localized plex scan.')
            ->addArgument('section', InputArgument::REQUIRED, 'Plex location section ID.')
            ->addArgument('directory', InputArgument::REQUIRED, 'Directory to scan.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $sectionId = $input->getArgument('section');
        $directory = $input->getArgument('directory');

        $cmd = str_replace(
            [
                '{section}',
                '{directory}',
            ],
            [
                (int)$sectionId,
                escapeshellarg($directory),
            ],
            $this->config['scanner']['cmd']
        );

        $output->writeln('<v>' . $cmd . '</v>', OutputInterface::VERBOSITY_VERBOSE);

        $process = Process::fromShellCommandline($cmd);

        $process
            ->setTty(false)
            ->setPty(true)
            ->setTimeout(null)
            ->run(
                function ($type, $line) use ($output) {
                    $output->writeln($line, OutputInterface::VERBOSITY_DEBUG);
                },
                $this->config['scanner']['env'] ?? []
            );
    }
}