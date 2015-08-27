<?php

namespace Dryrun;

use Symfony\Component\Process\Process;
use Symfony\Component\Filesystem\Filesystem;

class Github
{
    private $baseUrl;
    private $destination;

    public function __construct($url)
    {
        $this->baseUrl = $url;
        $this->setDestination();
    }

    private function setDestination()
    {
        $destination = array_slice(explode('/', str_replace('.git', '', $this->baseUrl)), -2, 2);
        $destination = implode('.', $destination);
        $this->destination = str_replace('git@github.com:', '', $destination);
    }

    public function isValidateUrl()
    {
        return preg_match('/git@|https?:\/\//', $this->baseUrl);
    }

    public function getClonableUrl()
    {
        $startsWithGit = (substr($this->baseUrl, 0, 4) === 'git@');
        $endsWithGit = (substr($this->baseUrl, 0, -strlen($this->baseUrl)) === '.git');

        if ($endsWithGit && !$startsWithGit) {
            return $this->baseUrl;
        }

        if (!$endsWithGit && $startsWithGit) {
            return $this->baseUrl.'.git';
        }

        return $this->baseUrl;
    }

    public function pull()
    {
        $clonable = $this->getClonableUrl();

        $tmpdir = sys_get_temp_dir().DS.'dryrun'.DS.$this->destination;

        $fs = new Filesystem();

        $fs->remove($tmpdir);

        $process = new Process("git clone $clonable $tmpdir");

        $process->run();

        return $tmpdir;
    }
}
