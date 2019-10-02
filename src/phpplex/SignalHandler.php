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

class SignalHandler
{
    /**
     * @var array
     */
    private $signals = [];


    /**
     * Bind helper's handlers
     */
    public function listen(): void
    {
        $handler = function ($code) {
            array_unshift($this->signals, $code);
        };

        // kill (default signal)
        pcntl_signal(SIGTERM, $handler);
        // Ctrl + C
        pcntl_signal(SIGINT, $handler);
        // kill -s HUP
        pcntl_signal(SIGHUP, $handler);
    }

    /**
     * Restore default handlers for signals
     */
    public function restoreDefaultHandlers(): void
    {
        pcntl_signal(SIGTERM, SIG_DFL);
        pcntl_signal(SIGINT, SIG_DFL);
        pcntl_signal(SIGHUP, SIG_DFL);
    }

    /**
     * Use cases:
     *  in_array(SIGHUP, $helper->takeSignals())
     * or
     *  array_intersect([SIGINT, SIGTERM], $helper->takeSignals())
     *
     * @return array List with signals (integer codes), can contains duplicates. Newest signal will be first.
     */
    public function takeSignals(): array
    {
        // All signals will caught only inside this call.
        pcntl_signal_dispatch();

        $signals = $this->signals;
        $this->signals = [];

        return $signals;
    }
}