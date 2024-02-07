<?php
declare(strict_types=1);

namespace App\Tests;

use App\Command\SearchUsersCommand;
use App\Service\CsvHandler;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class SearchUsersCommandTest extends TestCase
{
    public function testExecute(): void
    {
        $httpClient = $this->createMock(HttpClientInterface::class);
        $csvHandler = $this->createMock(CsvHandler::class);

        $csvHandler->method('readFromCsv')->willReturn([
            ['id' => 1, 'name' => 'ClareJohnson', 'salary' => '50000', 'tags' => 'cjohnson']
        ]);

        $application = new Application();
        $application->add(new SearchUsersCommand($httpClient, $csvHandler));

        $command = $application->find('app:search-users');
        $commandTester = new CommandTester($command);
        $commandTester->execute([
            '--tag' => ['cjohnson']
        ]);

        $output = $commandTester->getDisplay();
        $this->assertStringContainsString('User: ClareJohnson, salary: 50000', $output);
    }
}
