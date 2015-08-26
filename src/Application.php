<?php

namespace Dryrun;

use Symfony\Component\Console\Application as BaseApplication;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\OutputInterface;

class Application extends BaseApplication
{
    private $defaultCommand;

    /**
     * Sets the default Command name.
     *
     * @param string $commandName The Command name
     */
    public function setDefaultCommand($commandName)
    {
        $this->defaultCommand = $commandName;
    }

    /**
     * Runs the current application.
     *
     * @param InputInterface  $input  An Input instance
     * @param OutputInterface $output An Output instance
     *
     * @return int 0 if everything went fine, or an error code
     */
    public function doRun(InputInterface $input, OutputInterface $output)
    {
        if (true === $input->hasParameterOption(array('--version', '-V'))) {
            $output->writeln($this->getLongVersion());

            return 0;
        }

        $name = $this->getCommandName($input);
        if (true === $input->hasParameterOption(array('--help', '-h'))) {
            if (!$name) {
                $name = 'help';
                $input = new ArrayInput(array('command' => 'help'));
            } else {
                $this->wantHelps = true;
            }
        }

        try {
            // the command name MUST be the first element of the input
            $command = $this->find($name);
        } catch (\InvalidArgumentException $e) {

            //if no command is found , let the application handel it

            $command = $this->find($this->defaultCommand);
            $input = new ArrayInput(array('command' => $this->defaultCommand, 'url' => $name));
        }

        $this->runningCommand = $command;
        $exitCode = $this->doRunCommand($command, $input, $output);
        $this->runningCommand = null;

        return $exitCode;
    }
}
