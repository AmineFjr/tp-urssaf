<?php

namespace App\Service;

class StockJson
{
    public function sendDataToTextFile(array $data): void
    {
            $fileName = 'txt/' . $data["siren"] . '.txt';
            if (!is_file($fileName))
                file_put_contents($fileName, json_encode($data));

    }
}