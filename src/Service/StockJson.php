<?php

namespace App\Service;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpKernel\KernelInterface;

class StockJson
{
    private $kernel;

    public function __construct(KernelInterface $kernel)
    {
        $this->kernel = $kernel;
    }
    public function sendDataToTextFile(array $data): void
    {

        $filesystem = new Filesystem();
        $publicDirectory = $this->kernel->getProjectDir() . '/public';
        $filename = $publicDirectory . '/txt/companies.txt';

        if (!$filesystem->exists($filename)) {
            $filesystem->touch($filename);
        }
        $currentContent = file_get_contents($filename);

        $companies = json_decode($currentContent, true);
        if (!isset($companies[$data['siren']])) {
            $companies[$data['siren']] = $data;
            $filesystem->dumpFile($filename, json_encode($companies, JSON_PRETTY_PRINT));
        }
    }

}