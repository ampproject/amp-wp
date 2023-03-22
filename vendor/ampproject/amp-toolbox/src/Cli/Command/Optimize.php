<?php

namespace AmpProject\Cli\Command;

use AmpProject\Cli\Command;
use AmpProject\Cli\Options;
use AmpProject\Exception\Cli\InvalidArgument;
use AmpProject\Optimizer\ErrorCollection;
use AmpProject\Optimizer\TransformationEngine;

/**
 * Optimize AMP HTML markup and return optimized markup.
 *
 * @package ampproject/amp-toolbox
 */
final class Optimize extends Command
{
    /**
     * Name of the command.
     *
     * @var string
     */
    const NAME = 'optimize';

    /**
     * Help text of the command.
     *
     * @var string
     */
    const HELP_TEXT = 'Optimize AMP HTML markup and return optimized markup.';

    /**
     * Register the command.
     *
     * @param Options $options Options instance to register the command with.
     */
    public function register(Options $options)
    {
        $options->registerCommand(self::NAME, self::HELP_TEXT);

        $options->registerArgument('file', "File with unoptimized AMP markup. Use '-' for STDIN.", true, self::NAME);
    }

    /**
     * Process the command.
     *
     * Arguments and options have been parsed when this is run.
     *
     * @param Options $options Options instance to process the command with.
     *
     * @throws InvalidArgument If the provided file is not readable.
     */
    public function process(Options $options)
    {
        list($file) = $options->getArguments();

        if (
            $file !== '-'
            &&
            (
                !is_file($file)
                ||
                !is_readable($file)
            )
        ) {
            throw InvalidArgument::forUnreadableFile($file);
        }

        if ($file === '-') {
            $file = 'php://stdin';
        }

        $html          = file_get_contents($file);
        $optimizer     = new TransformationEngine();
        $errors        = new ErrorCollection();
        $optimizedHtml = $optimizer->optimizeHtml($html, $errors);

        echo($optimizedHtml . PHP_EOL);
    }
}
