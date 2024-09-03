<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Controller\Traits\IsUserAssociated;
use App\Entity\Job;
use App\Repository\JobRepository;
use App\Repository\TaskRepository;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Exception\NotEncodableValueException;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('/api/v1', name: 'api_')]
class JobController extends AbstractController
{
    use IsUserAssociated;

    public function __construct(
        private TaskRepository $taskRepository,
        private JobRepository $jobRepository,
        private SerializerInterface $serializer,
        private LoggerInterface $logger,
        private RequestStack $requestStack,
    ) {
    }

    #[Route('/tasks/{task_id}/jobs', name: 'jobs_list', methods: ['GET'], format: 'json')]
    public function jobs(Request $request): Response
    {
        $taskId = $request->get('task_id');

        $task = $this->taskRepository->findOneById($taskId);
        if (!$task) {
            throw $this->createNotFoundException('Task not found.');
        }

        $json = $this->serializer->normalize($task->getJobs(), 'json');

        return new JsonResponse($json);
    }

    #[Route('/tasks/{task_id}/jobs', name: 'jobs_create', methods: ['POST'], format: 'json')]
    public function create(Request $request): Response
    {
        $taskId = $request->get('task_id');
        $task = $this->taskRepository->findOneById($taskId);
        if (!$task) {
            throw $this->createNotFoundException('Task not found');
        }

        if (!$this->isUserAssociated($task)) {
            throw $this->createAccessDeniedException('Access Denied.');
        }

        try {
            $job = $this->serializer->deserialize($request->getContent(), Job::class, 'json');
        } catch (NotEncodableValueException $e) {
            return new Response(
                'Unable to serialize.',
                Response::HTTP_BAD_REQUEST,
                ['content-type' => 'application/json']
            );
        }

        $task->addJob($job);

        $this->jobRepository->save($job, true, true);

        return new Response(
            $job->getId()->toBase32(),
            Response::HTTP_CREATED,
            ['content-type' => 'application/json']
        );
    }

    #[Route('/tasks/{task_id}/jobs/{job_id}', name: 'jobs_update', methods: ['PATCH'], format: 'json')]
    public function update(Request $request): Response
    {
        $taskId = $request->get('task_id');
        $task = $this->taskRepository->findOneById($taskId);
        if (!$task) {
            throw $this->createNotFoundException('Task not found');
        }

        if (!$this->isUserAssociated($task)) {
            throw $this->createAccessDeniedException('Access Denied.');
        }

        $jobId = $request->get('job_id');
        $job = $this->jobRepository->findOneById($jobId);
        if (!$job) {
            throw $this->createNotFoundException('No job found');
        }

        try {
            $tmpJob = $this->serializer->deserialize($request->getContent(), Job::class, 'json');
        } catch (NotEncodableValueException $e) {
            return new Response(
                'Unable to serialize.',
                Response::HTTP_BAD_REQUEST,
                ['content-type' => 'application/json']
            );
        }

        $job->setTitle($tmpJob->getTitle());
        $job->setDescription($tmpJob->getDescription());
        $job->setNotes($tmpJob->getNotes());
        $job->setIsCompleted($tmpJob->isCompleted());

        $this->jobRepository->save($job, true);

        return new Response(
            $job->getId()->toBase32(),
            Response::HTTP_OK,
            ['content-type' => 'application/json']
        );
    }

    #[Route('/tasks/{task_id}/jobs/{job_id}', name: 'jobs_delete', methods: ['DELETE'], format: 'json')]
    public function delete(Request $request): Response
    {
        $taskId = $request->get('task_id');
        $task = $this->taskRepository->findOneById($taskId);
        if (!$task) {
            throw $this->createNotFoundException('Task not found.');
        }

        if (!$this->isUserAssociated($task)) {
            throw $this->createAccessDeniedException('Access Denied.');
        }

        $jobId = $request->get('job_id');
        $job = $this->jobRepository->findOneById($jobId);
        if (!$job) {
            throw $this->createNotFoundException('Job not found');
        }

        $this->jobRepository->remove($job, true);

        return new Response(
            $jobId,
            Response::HTTP_OK,
            ['content-type' => 'application/json']
        );
    }
}
