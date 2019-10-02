<?php declare(strict_types=1, ticks=1);
/*
 * This file is part of the PHPPlex package.
 *
 * (c) Abdulmohsen A. (admin@arabcoders.rog)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace phpplex\Commands;

use phpplex\Filters\PregFilter;
use phpplex\Filters\TextFilter;
use phpplex\Plex;
use phpplex\Command;
use phpplex\SignalHandler;
use phpplex\UnderControl;
use Symfony\Component\Process\Process;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @author Abdulmohsen A. <admin@arabcoders.rog>
 */
class Run extends Command
{
    /**
     * @var OutputInterface
     */
    protected $output;
    /**
     * @var PregFilter
     */
    protected $allow, $exclude, $match;

    /**
     * @var UnderControl
     */
    protected $underControl;

    /**
     * @var string Media Path.
     */
    protected $mediaPath = '';
    /**
     * @var Process
     */
    private $process;

    protected function configure(): void
    {
        $this->setName('run')
            ->setDescription('Run Lazy scanner.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->output = $output;

        $signals = new SignalHandler();

        $signals->listen();

        $this->mediaPath = rtrim($this->config['media_path'], '/') . '/';

        $output->writeln(
            sprintf(
                '<d>Calling Plex with %s://%s:%d?X-Plex-Token=%s</d>',
                $this->config['plex_ssl'] ? 'https' : 'http',
                $this->config['plex_host'],
                $this->config['plex_port'],
                substr($this->config['plex_token'], 0, 1) .
                str_repeat('*', strlen($this->config['plex_token']) - 2) .
                substr($this->config['plex_token'], -1)
            ),
            OutputInterface::VERBOSITY_VERBOSE
        );

        $plex = new Plex(
            $this->config['plex_host'],
            $this->config['plex_port'],
            $this->config['plex_token'],
            $this->config['plex_ssl']
        );

        $locations = $plex->getLocations();

        $output->writeln($plex->getLastCommand(), OutputInterface::VERBOSITY_VERY_VERBOSE);

        $output->writeln(print_r($locations, true), OutputInterface::VERBOSITY_VERY_VERBOSE);

        switch (strtolower($this->config['log_type'] ?? '')) {
            case 'file':
                if (!file_exists($this->config['log_location']) || !is_readable($this->config['log_location'])) {
                    throw new \InvalidArgumentException(
                        sprintf('Logfile does not exists or is unreadable. - \'%s\' ',
                            $this->config['log_location']
                        )
                    );
                }

                $output->writeln(
                    sprintf('<d>Ok, We are able to read the logfile - \'%s\' </d>', $this->config['log_location']),
                    OutputInterface::VERBOSITY_VERY_VERBOSE
                );

                $cmd = [
                    'tail',
                    '-n',
                    (int)$this->config['log_offset'],
                    '-F',
                    $this->config['log_location']
                ];

                break;
            case 'journal':
                $cmd = [
                    'journalctl',
                    '-n',
                    (int)$this->config['log_offset'],
                    '-f',
                    '-u',
                    $this->config['log_location']
                ];
                break;
            default:
                throw new \RuntimeException(
                    sprintf(
                        'log_type of \'%s\' is not supported. only (file|journal) are supported.',
                        $this->config['log_type']
                    )
                );
                break;
        }

        $this->underControl = new UnderControl($locations);

        $this->allow = new PregFilter($this->config['files_allow']);

        $this->exclude = new TextFilter($this->config['files_exclude']);

        if ('VFS' === strtoupper($this->config['log_match_type'] ?? '')) {

            $this->match = new PregFilter($this->config['log_match_vfs']);

            if ($this->config['log_cmd_grep']) {
                $cmd = implode(' ', $cmd) . ' | grep --color=never ' . escapeshellarg($this->config['log_match_vfs']);
            }

        } else {

            $this->match = new PregFilter($this->config['log_match_cache']);

            if ($this->config['log_cmd_grep']) {
                $cmd = implode(' ', $cmd) . ' | grep --color=never ' . escapeshellarg($this->config['log_match_cache']);
            }

        }

        $output->writeln('<d>' . (is_array($cmd) ? implode(' ', $cmd) : $cmd) . '</d>',
            OutputInterface::VERBOSITY_VERY_VERBOSE);

        $this->process = is_array($cmd) ? new Process($cmd) : Process::fromShellCommandline($cmd);

        $this->process
            ->setTty(false)
            ->setPty(true)
            ->setTimeout(null)
            ->start(function ($type, $line) {
                $this->output->writeln($line, OutputInterface::VERBOSITY_DEBUG);
                $this->matcher($line);
            });

        while ($this->process->isRunning()) {

            if (count(array_intersect([SIGINT, SIGTERM], $signals->takeSignals())) > 0) {

                if (null !== $this->process && $this->process->isRunning()) {

                    $this->output->writeln('Sending TERM Signal.', OutputInterface::VERBOSITY_DEBUG);

                    $this->process->signal(SIGKILL);
                }

                break;
            }

            sleep(1);
        }
    }

    private function matcher($line): void
    {
        $match = $this->match->run($line);

        if (!$match->isFound()) {
            $this->output->writeln(
                '<d>Not A match.</d> - ' . $match->getLine(),
                OutputInterface::VERBOSITY_DEBUG
            );
            return;
        }

        $allow = $this->allow->run($match->getFirst());

        if (!$allow->isFound()) {

            $this->output->writeln(
                '<e>Not whitelisted in allowed files_allow</e> -' . $allow->getLine(),
                OutputInterface::VERBOSITY_VERY_VERBOSE
            );

            return;
        }

        $exclude = $this->exclude->run($match->getFirst());

        if ($exclude->isFound()) {
            $this->output->writeln(
                '<e>File is excluded in files_exclude</e> -' . $exclude->getLine(),
                OutputInterface::VERBOSITY_VERY_VERBOSE
            );

            return;
        }

        $this->output->writeln('<s>' . $match->getFirst() . '</s>', OutputInterface::VERBOSITY_DEBUG);

        $filePath = $this->mediaPath . $match->getFirst();

        if (!is_file($filePath)) {
            $this->output->writeln('<e>Detected \'%s\' as valid match, but it\'s not file.</e>',
                OutputInterface::VERBOSITY_VERBOSE);
            return;
        }

        $file = new \SplFileObject($filePath);

        if (!$this->underControl->isMatch($file)) {
            $this->output->writeln(
                '<e>File is Not Under Control</e> -' . $exclude->getLine(),
                OutputInterface::VERBOSITY_VERY_VERBOSE
            );

            return;
        }

        $this->output->writeln(
            '<s>' . $this->underControl->getSection() . ':' . $this->underControl->getDirectory() . '</s>/' . $file->getFilename(),
            OutputInterface::VERBOSITY_VERBOSE);

        $this->executeSubCommand('scan', [
            'section' => $this->underControl->getSection(),
            'directory' => $this->underControl->getDirectory()
        ], $this->output);
    }

}