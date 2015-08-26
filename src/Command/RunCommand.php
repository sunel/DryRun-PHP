<?php

namespace Dryrun\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Dryrun\Github;
use Dryrun\Android;
use Dryrun\InvalidPathExcetion;

class RunCommand extends Command
{
    protected function configure()
    {
        $this->setName('run')
            ->setDescription('Outputs \'Hello World\'')
            ->addArgument(
                'url',
                InputArgument::REQUIRED,
                'The Git Url'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $url = $input->getArgument('url');

        $git = new Github($url);

        if (!$git->isValidateUrl()) {
            $output->writeln('<error>Not a valid GIT url.</error>');

            return;
        }
        if (!Android::isHomeDefined()) {
            $output->writeln('<error>WARNING: your <bg=yellow;options=bold>$ANDROID_HOME</> is not defined.</error>');
            //return;
        }

        $output->writeln('<info>Cloning into Temp Dir</info>');

        $repositoryPath = $git->pull();

        $androidProject = new Android($repositoryPath);

        if (!$androidProject->isValid()) {
            $output->writeln("<error>$url is not a valid android project</error>");

            return;
        }
        try {
            $androidProject->install();
        } catch (InvalidPathExcetion $e) {
            $output->writeln("<error>{$e->getMessage()}</error>");

            return;
        }

        $output->writeln("<fg=green>If you want to remove the app you just installed, execute:\n <bg=yellow;options=bold>{$androidProject->getUninstallCommand()}</></>");
    }
}
