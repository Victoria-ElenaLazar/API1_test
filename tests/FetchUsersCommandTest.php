<?php
declare(strict_types=1);

namespace App\Tests;

use App\Command\FetchUsersCommand;
use App\Service\CsvHandler;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

class FetchUsersCommandTest extends TestCase
{
    /**
     * @return void
     * @throws \PHPUnit\Framework\MockObject\Exception
     */
    public function testExecute()
    {
        $httpClientMock = $this->createMock(HttpClientInterface::class);
        $csvHandlerMock = $this->createMock(CsvHandler::class);

        $firstApiResponseMock = $this->createMock(ResponseInterface::class);
        $firstApiResponseMock->method('getStatusCode')->willReturn(200);
        $firstApiResponseMock->method('getContent')->willReturn(json_encode(['users' => [['id' => 1, 'name' => 'John'], ['id' => 2, 'name' => 'Jane']]]));

        $httpClientMock->method('request')->willReturn($firstApiResponseMock);

        $csvFilePath = tempnam(sys_get_temp_dir(), 'test_csv');
        $csvHandlerMock->method('getOutputCsvFilePath')->willReturn($csvFilePath);

        $command = new FetchUsersCommand($httpClientMock, $csvHandlerMock);

        $commandTester = new CommandTester($command);

        $commandTester->execute([]);

        $output = $commandTester->getDisplay();
        $this->assertStringContainsString('Users fetched and saved to', $output);

        $this->assertFileExists($csvFilePath);

        unlink($csvFilePath);
    }
}
