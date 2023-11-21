<?php

namespace App\Service;

class StockJson
{
    public function sendDataToTextFile(array $data): void
    {
        $siren = $data["Siren"];
        $fileName = 'txt/' . $siren . '.txt';
        if (!is_file($fileName)) {
            file_put_contents($fileName, json_encode($data));
        }
    }

    public function fileExist(string $siren): bool
    {
        $fileName = 'txt/' . $siren . '.txt';
        return is_file($fileName);
    }



    public function getDataFromTextFile(string $siren): array
    {
        if (is_file('txt/' . $siren . '.txt'))
            return json_decode(file_get_contents('txt/' . $siren . '.txt'), true);
        return [];
    }

    public function getAllDataFromTextFile(): array
    {
        $data = [];
        $files = scandir('txt/');
        foreach ($files as $file) {
            if ($file != "." && $file != "..") {
                $data[] = json_decode(file_get_contents('txt/' . $file), true);
            }
        }
        return $data;
    }

    public function deleteDataFromTextFile(string $siren): void
    {
        if (is_file('txt/' . $siren . '.txt'))
            unlink('txt/' . $siren . '.txt');
    }

    public function updateDataFromTextFile(array $data, string $siren): void
    {
        $fileName = 'txt/' . $siren . '.txt';
        if (is_file($fileName))
            file_put_contents($fileName, json_encode($data));
    }
}