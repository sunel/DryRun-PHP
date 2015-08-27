<?php

namespace Dryrun;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Process\Process;
use Symfony\Component\Console\Output\OutputInterface;

class Android
{
    private $basePath;
    private $settingsGradlePath;
    private $modules;
    private $package;
    private $launcherActivity;
    private $outputInterface;
    private $pathToSample;

    public function __construct($path)
    {
        $this->basePath = $path;
        $this->setGradlePath();
        $this->setModules();
    }

    public static function isHomeDefined()
    {
        return !empty(getenv('ANDROID_HOME'));
    }

    public function isValid()
    {
        return file_exists($this->settingsGradlePath);
    }

    public function install()
    {
        list($path, $executeLine) = $this->sampleProject();

        if ($path == false and $executeLine == false) {
            throw new InvalidPathExcetion("Couldn't open, sorry!");
        }

        $builder = $this->basePath.DS.'gradle';

        if (file_exists($this->basePath.DS.'gradlew')) {
            $process = new Process('chmod +x '.$this->basePath.DS.'gradlew');

            $process->run();

            $builder = 'sh '.$this->basePath.DS.' gradlew';
        }

        if (file_exists($this->basePath.DS.'gradlew') && $this->isGradleWrapped()) {
            $process = new Process($this->basePath.DS.'gradle wrap ');

            $process->run();
        }

        $this->uninstall();
        $this->removeApplicationId();
        $this->removeLocalProperties();

        $process = new Process($builder.' clean assembleDebug installDebug');

        $process->run(function ($type, $buffer) {
            if (Process::ERR === $type) {
                $this->outputInterface->writeln("<error>ERR > .$buffer</error>");
            } else {
                $this->outputInterface->writeln("<info>OUT > .$buffer</info>");
            }
        });

        $this->outputInterface->writeln("\n<fg=green>Installing {$this->package}...</>");
        $this->outputInterface->writeln("\n<fg=green>Installing {$executeLine}...</>");

        $process = new Process($executeLine);

        $process->run(function ($type, $buffer) {
            if (Process::ERR === $type) {
                $this->outputInterface->writeln("<error>ERR > .$buffer</error>");
            } else {
                $this->outputInterface->writeln("<info>OUT > .$buffer</info>");
            }
        });
    }

    public function getUninstallCommand()
    {
        return "adb uninstall {$this->package}";
    }

    public function setOutputInterface(OutputInterface $output)
    {
        $this->outputInterface = $output;
    }

    private function uninstall()
    {
        $process = new Process($this->getUninstallCommand());

        $process->run();
    }

    private function removeApplicationId()
    {
        $tmpfname = tempnam(sys_get_temp_dir(), 'FOO');
        $temp = fopen($tmpfname, 'w');

        $file = $this->pathToSample.DS.'build.gradle';
        $handle = fopen($file, 'r');

        while (($line = fgets($handle)) !== false) {
            if (strpos($line, 'applicationId') === false) {
                fwrite($temp, $line);
            }
        }

        fclose($handle);
        fclose($temp);

        $fs = new Filesystem();
        $fs->rename($tmpfname, $file);
    }

    private function removeLocalProperties()
    {
        $fileName = $this->basePath.DS.'local.properties';
        if (file_exists($fileName)) {
            $fs = new Filesystem();
            $fs->remove($fileName);
        }
    }

    private function isGradleWrapped()
    {
        if (!file_exists($this->basePath.DS.'gradle/')) {
            return false;
        }

        return file_exists($this->basePath.DS.'gradle/wrapper/gradle-wrapper.properties')
              && file_exists($this->basePath.DS.'gradle/wrapper/gradle-wrapper.jar');
    }
    private function sampleProject()
    {
        foreach ($this->modules as $module) {
            $this->pathToSample = $fullPath = $this->basePath.DS.$module;

            $executeLine = $this->getExecuteLine($fullPath.DS.'src'.DS.'main'.DS.'AndroidManifest.xml');
            if ($executeLine) {
                return [$fullPath, $executeLine];
            }
        }

        return [false,false];
    }

    private function getExecuteLine($pathToSample)
    {
        if (!file_exists($pathToSample)) {
            return false;
        }

        $handle = fopen($pathToSample, 'r');
        $contents = fread($handle, filesize($pathToSample));
        $doc = simplexml_load_string($contents);

        $this->package = $this->getPackage($doc);
        $this->launcherActivity = $this->getLauncherActivity($doc);

        if (!$this->launcherActivity) {
            return false;
        }

        fclose($handle);

        return "adb shell am start -n \"{$this->getLaunchableActivity()}\" -a android.intent.action.MAIN -c android.intent.category.LAUNCHER";
    }

    private function setGradlePath()
    {
        $this->settingsGradlePath = $this->basePath.DS.'settings.gradle';
    }

    private function setModules()
    {
        if ($this->isValid()) {
            $handle = fopen($this->settingsGradlePath, 'rb');
            $contents = fread($handle, filesize($this->settingsGradlePath));

            preg_match_all("/'([^']*)'/", $contents, $matches, PREG_SET_ORDER);

            fclose($handle);

            $matches = array_map(function ($value) {
                return str_replace(':', '/', $value[1]);
            }, $matches);

            $this->modules = $matches;
        }
    }

    private function getLaunchableActivity()
    {
        $fullPathToLauncher = $this->package.str_replace($this->package, '', $this->launcherActivity);

        return $this->package.DS.$fullPathToLauncher;
    }

    private function getPackage($doc)
    {
        return (string) $doc->attributes()['package'];
    }
    private function getLauncherActivity($doc)
    {
        foreach ($doc->xpath('application/activity') as $activity) {
            if (count($activity->xpath('intent-filter'))) {
                return (string) $activity->xpath('@android:name')[0];
            }
        }

        return false;
    }
}
