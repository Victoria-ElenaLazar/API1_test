<?php
declare(strict_types=1);

namespace App\Service;

use Symfony\Component\Serializer\Encoder\CsvEncoder;

class CsvHandler
{
    /**
     * @param array $data
     * @param string $filePath
     * @return void
     * Write the data fetched into the csv file
     */
    public function writeToCsv(array $data, string $filePath): void
    {
        $encoder = new CsvEncoder();
        file_put_contents($filePath, $encoder->encode($data, 'csv'));
    }

    /**
     * @param string $filePath
     * @return array
     * Read the data
     */
    public function readFromCsv(string $filePath): array
    {
        $csvData = [];
        $decoder = new CsvEncoder();
        if (file_exists($filePath)) {
            $csvContent = file_get_contents($filePath);
            $csvData = $decoder->decode($csvContent, 'csv');
        }
        return $csvData;
    }

    /**
     * @return string
     * Save the file in 'data' folder as 'users.csv'
     */
    public function getOutputCsvFilePath(): string
    {
        return __DIR__ . '/../../data/users.csv';
    }
}