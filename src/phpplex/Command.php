<?php declare(strict_types=1);
/*
 * This file is part of the PHPPlex package.
 *
 * (c) Abdulmohsen A. (admin@arabcoders.rog)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace phpplex;

use \Symfony\Component\Console\Command\Command as BaseCommand;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Command Extender.
 *
 * @author Abdulmohsen A. <admin@arabcoders.rog>
 */
class Command extends BaseCommand
{
    protected $config = [];

    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        if (null !== $this->getApplication()) {
            $this->config = $this->getApplication()->getConfig();
        }

        $outputStyle = new OutputFormatterStyle('cyan');
        $output->getFormatter()->setStyle('d', $outputStyle);

        $outputStyle = new OutputFormatterStyle('white');
        $output->getFormatter()->setStyle('v', $outputStyle);

        $outputStyle = new OutputFormatterStyle('green');
        $output->getFormatter()->setStyle('s', $outputStyle);

        $outputStyle = new OutputFormatterStyle('red');
        $output->getFormatter()->setStyle('e', $outputStyle);

        parent::initialize($input, $output);
    }

    protected function executeSubCommand(string $name, array $parameters, OutputInterface $output): ?int
    {
        $output->writeln(
            sprintf(
                '<d>Running subcommand \'%s\' with the following parameters ( %s )</d>',
                $name,
                http_build_query($parameters)
            ),
            OutputInterface::VERBOSITY_DEBUG
        );

        if (null !== $this->getApplication()) {
            return $this->getApplication()->find($name)->run(new ArrayInput($parameters), $output);
        }

        return null;
    }
}