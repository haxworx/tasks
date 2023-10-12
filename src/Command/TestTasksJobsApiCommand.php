<?php

declare(strict_types=1);

namespace App\Command;

use App\Entity\Job;
use App\Entity\Task;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

#[AsCommand(
    name: 'app:api',
    description: 'Test the API.',
    hidden: false,
    aliases: ['app:api']
)]
class TestTasksJobsApiCommand extends Command
{
    public function __construct(
        private HttpClientInterface $client,
        private SerializerInterface $serializer,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $headers = [
            'Authorization' => 'Bearer f229350d-13f5-41e5-bb94-5ecdb109cc52',
        ];

        $output->writeln('Testing task GET');
        $response = $this->client->request(
            'GET',
            'http://localhost:8000/api/v1/tasks',
            [
                'headers' => $headers,
            ]
        );

        $output->writeln($response->getContent(false));
        $output->writeln(sprintf('status code %d', $response->getStatusCode()));

        $output->writeln('Testing task POST');

        $task = new Task();
        $task->setTitle('Example Task');
        $task->setDescription('Description');
        $task->setTimeStart(new \DateTimeImmutable('now'));
        $task->setTimeFinish(new \DateTimeImmutable('+1 hour'));

        $json = $this->serializer->serialize($task, 'json');

        $response = $this->client->request(
            'POST',
            'http://localhost:8000/api/v1/tasks',
            [
                'headers' => $headers,
                'body' => $json,
            ],
        );

        $ulid = $response->getContent(false);
        $output->writeln($ulid);
        $output->writeln(sprintf('status code %d', $response->getStatusCode()));

        $output->writeln('Testing task PATCH');

        $task->setDescription('We have edited');

        $json = $this->serializer->serialize($task, 'json');

        $response = $this->client->request(
            'PATCH',
            sprintf('http://localhost:8000/api/v1/tasks/%s', $ulid),
            [
                'headers' => $headers,
                'body' => $json,
            ]
        );

        $output->writeln($response->getContent(false));
        $output->writeln(sprintf('status code %d', $response->getStatusCode()));

        $output->writeln('Testing job GET');

        $response = $this->client->request(
            'GET',
            sprintf('http://localhost:8000/api/v1/tasks/%s/jobs', $ulid),
            [
                'headers' => $headers,
            ]
        );

        $output->writeln($response->getContent(false));
        $output->writeln(sprintf('status code %d', $response->getStatusCode()));

        $output->writeln('Testing job POST');

        $job = new Job();
        $job->setTitle('A job');
        $job->setDescription('A test job');
        $job->setNotes('Some notes');
        $job->setIsCompleted(false);

        $json = $this->serializer->serialize($job, 'json');

        $response = $this->client->request(
            'POST',
            sprintf('http://localhost:8000/api/v1/tasks/%s/jobs', $ulid),
            [
                'headers' => $headers,
                'body' => $json,
            ]
        );

        $jobUlid = $response->getContent();
        $output->writeln(sprintf('status code %d', $response->getStatusCode()));

        $output->writeln('Testing job PATCH');

        $job->setDescription('A job updated');
        $job->setNotes('Some updated notes');
        $job->setIsCompleted(true);

        $json = $this->serializer->serialize($job, 'json');

        $response = $this->client->request(
            'PATCH',
            sprintf('http://localhost:8080/api/v1/tasks/%s/jobs/%s', $ulid, $jobUlid),
            [
                'headers' => $headers,
                'body' => $json,
            ]
        );

        $output->writeln($response->getContent(false));
        $output->writeln(sprintf('status code %d', $response->getStatusCode()));

        $output->writeln('Testing job DELETE');

        $response = $this->client->request(
            'DELETE',
            sprintf('http://localhost:8080/api/v1/tasks/%s/jobs/%s', $ulid, $jobUlid),
            [
                'headers' => $headers,
            ]
        );

        $output->writeln($response->getContent(false));
        $output->writeln(sprintf('status code %d', $response->getStatusCode()));

        $output->writeln('Testing task DELETE');

        $response = $this->client->request(
            'DELETE',
            sprintf('http://localhost:8080/api/v1/tasks/%s', $ulid),
            [
                'headers' => $headers,
            ]
        );

        $output->writeln(sprintf('status code %d', $response->getStatusCode()));

        return Command::SUCCESS;
    }
}
