<?php
declare(strict_types=1);

namespace App\Command;

use App\Service\CsvHandler;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

#[AsCommand(name: 'app:search-users')]
class SearchUsersCommand extends Command
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
        $this->setDescription('Search users with specific tags in the CSV file');
        $this->addOption('tag', 't', InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY, 'Tags to search for in users');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     *  Command to read the data from the csv file by tags.
     *  If there are data inside the users.csv file, output the content.
     *  Handle errors for tags, content and csv existence.
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $tags = (array)$input->getOption('tag');

        if (empty($tags)) {
            $output->writeln('No tags provided.');
            return Command::FAILURE;
        }

        $csvFilePath = __DIR__ . '/../../data/users.csv';

        if (!file_exists($csvFilePath)) {
            $output->writeln('CSV file does not exist.');
            return Command::FAILURE;
        }

        $users = $this->csvHandler->readFromCsv($csvFilePath);

        if (empty($users)) {
            $output->writeln('No users found in the CSV file.');
            return Command::FAILURE;
        }

        $filteredUsers = $this->filterUsersByTags($users, $tags);

        if (empty($filteredUsers)) {
            $output->writeln('No users found with the provided tags.');
            return Command::FAILURE;
        }

        foreach ($filteredUsers as $user) {
            $output->writeln(sprintf('User: %s, salary: %s', $user['name'], $user['salary']));
        }

        return Command::SUCCESS;
    }

    /**
     * @param array $users
     * @param array $tags
     * @return array
     * return the users by specific tags
     */
    protected function filterUsersByTags(array $users, array $tags): array
    {
        return array_filter($users, function ($user) use ($tags) {
            $userTags = is_array($user['tags']) ? $user['tags'] : [$user['tags']];

            return isset($user['tags']) && count(array_intersect($tags, $userTags)) === count($tags);
        });
    }
}
