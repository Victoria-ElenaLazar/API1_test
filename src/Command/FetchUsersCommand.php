<?php
declare(strict_types=1);

namespace App\Command;

use App\Service\CsvHandler;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

#[AsCommand(name: 'app:fetch-users')]
class FetchUsersCommand extends Command
{
    private HttpClientInterface $httpClient;
    private CsvHandler $csvHandler;

    public function __construct(HttpClientInterface $httpClient, CsvHandler $csvHandler)
    {
        parent::__construct();
        $this->httpClient = $httpClient;
        $this->csvHandler = $csvHandler;
    }

    protected function configure(): void
    {
        $this->setDescription('Fetch users from two external endpoints and save to CSV');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     * @throws \Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     *
     * Command to fetch users from two different links and save data into data/users.csv.
     * If the request for the first link fail, attempt to reach the second link.
     * Handle errors for fetching data and decoding data accordingly
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        try {
            $userData = [];

            $firstApiResponse = $this->httpClient->request('GET', 'https://run.mocky.io/v3/03d2a7bd-f12f-4275-9e9a-84e41f9c2aae?verbose=1');
            if ($firstApiResponse->getStatusCode() === 200) {
                $responseData = $firstApiResponse->getContent();
                $data = json_decode($responseData, true);
                $userData = $data['users'] ?? [];
            } else {
                $output->writeln('Error: Failed to fetch data from the first API.');
                $output->writeln('Attempting to fetch data from the second API.');

                $secondApiResponse = $this->httpClient->request('GET', 'https://run.mocky.io/v3/aab281fe-3dbb-4d91-a863-a96e6bf083d7?expose=1');
                if ($secondApiResponse->getStatusCode() === 200) {
                    $responseData = $secondApiResponse->getContent();
                    $data = json_decode($responseData, true);
                    $userData = $data['users'] ?? [];
                } else {
                    $output->writeln('Error: Failed to fetch data from the second API.');
                    return Command::FAILURE;
                }
            }

            if (empty($userData)) {
                $output->writeln('No user data retrieved from both APIs.');
                return Command::FAILURE;
            }

            $csvFilePath = $this->csvHandler->getOutputCsvFilePath();
            $this->csvHandler->writeToCsv($userData, $csvFilePath);

            if (!file_exists($csvFilePath)) {
                $output->writeln('Error: Failed to create CSV file.');
                return Command::FAILURE;
            }

            $output->writeln(sprintf('Users fetched and saved to %s', $csvFilePath));

            return Command::SUCCESS;
        } catch (ClientExceptionInterface $e) {
            $output->writeln('Error: ' . $e->getMessage());
            return Command::FAILURE;
        } catch (\JsonException $e) {
            $output->writeln('Error: Failed to decode JSON response from API');
            return Command::FAILURE;
        } catch (\Exception $e) {
            $output->writeln('Error: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
