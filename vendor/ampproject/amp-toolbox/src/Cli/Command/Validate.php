<?php

namespace AmpProject\Cli\Command;

use AmpProject\Cli\Colors;
use AmpProject\Cli\Command;
use AmpProject\Cli\Options;
use AmpProject\Cli\TableFormatter;
use AmpProject\Exception\Cli\InvalidArgument;
use AmpProject\Validator\ValidationEngine;
use AmpProject\Validator\ValidationStatus;

/**
 * Validate AMP HTML markup and return validation errors.
 *
 * @package ampproject/amp-toolbox
 */
final class Validate extends Command
{
    /**
     * Name of the command.
     *
     * @var string
     */
    const NAME = 'validate';

    /**
     * Help text of the command.
     *
     * @var string
     */
    const HELP_TEXT = 'Validate AMP HTML markup and return validation errors.';

    /**
     * Register the command.
     *
     * @param Options $options Options instance to register the command with.
     */
    public function register(Options $options)
    {
        $options->registerCommand(self::NAME, self::HELP_TEXT);

        $options->registerArgument('file', "File with AMP markup to validate. Use '-' for STDIN.", true, self::NAME);
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

        $html      = file_get_contents($file);
        $validator = new ValidationEngine();
        $result    = $validator->validateHtml($html);

        foreach ($result->getErrors() as $error) {
            echo sprintf(
                "%d:%d [%s] %s (%s)\n",
                $error->getLine(),
                $error->getColumn(),
                $error->getSeverity(),
                $error->getCode(),
                implode(', ', $error->getParams())
            );
        }

        switch ($result->getStatus()->asInt()) {
            case ValidationStatus::PASS:
                $this->cli->success('Validation SUCCEEDED.');
                exit(0);
            case ValidationStatus::FAIL:
                $this->cli->error('Validation FAILED!');
                exit(1);
            case ValidationStatus::UNKNOWN:
                $this->cli->critical('Validation produced an UNKNOWN state!');
                exit(128);
        }
    }
}
