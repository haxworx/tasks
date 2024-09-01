<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Entity\Task;
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
class TaskController extends AbstractController
{
    public function __construct(
        private TaskRepository $taskRepository,
        private LoggerInterface $logger,
        private SerializerInterface $serializer,
        private RequestStack $requestStack,
    ) {
    }

    #[Route('/tasks', name: 'tasks_list', methods: ['GET'], format: 'json')]
    public function tasks(): Response
    {
        $tasks = $this->taskRepository->findAllByUserId($this->getUser()->getId());

        $json = $this->serializer->normalize($tasks, 'json');

        return new JsonResponse($json);
    }

    #[Route('/tasks', name: 'tasks_create', methods: ['POST'], format: 'json')]
    public function create(Request $request): Response
    {
        try {
            $task = $this->serializer->deserialize($request->getContent(), Task::class, 'json');
        } catch (NotEncodableValueException $e) {
            return new Response(
                'Unable to serialize.',
                Response::HTTP_BAD_REQUEST,
                ['content-type' => 'application/json']
            );
        }

        $task->setUserId($this->getUser()->getId());
        $this->taskRepository->save($task, true, true);

        return new Response(
            $task->getId()->toBase32(),
            Response::HTTP_CREATED,
            ['content-type' => 'application/json']
        );
    }

    #[Route('/tasks/{id}', name: 'tasks_update', methods: ['PATCH'], format: 'json')]
    public function update(Request $request, string $id): Response
    {
        try {
            $tmpTask = $this->serializer->deserialize($request->getContent(), Task::class, 'json');
        } catch (NotEncodableValueException $e) {
            return new Response(
                'Unable to serialize.',
                Response::HTTP_BAD_REQUEST,
                ['content-type' => 'application/json']
            );
        }

        $task = $this->taskRepository->findOneById($id);
        if (!$task) {
            throw $this->createNotFoundException('Task not found.');
        }

        if (!$this->isUserAssociated($task)) {
            throw $this->createAccessDeniedException('Access Denied.');
        }

        $task->setTitle($tmpTask->getTitle());
        $task->setDescription($tmpTask->getDescription());
        $task->setNotes($tmpTask->getNotes());
        $task->setTimeStart($tmpTask->getTimeStart());
        $task->setTimeFinish($tmpTask->getTimeFinish());
        $task->setIsCompleted($tmpTask->isCompleted());
        $task->setInProgress($tmpTask->isInProgress());
        $task->setPriority($tmpTask->getPriority());

        $this->taskRepository->save($task, false, true);

        return new Response(
            $task->getId()->toBase32(),
            Response::HTTP_OK,
            ['content-type' => 'application/json']
        );
    }

    #[Route('/tasks/{id}', name: 'tasks_delete', methods: ['DELETE'], format: 'json')]
    public function delete(Request $request, string $id): Response
    {
        $task = $this->taskRepository->findOneById($id);
        if (!$task) {
            throw $this->createNotFoundException('Task not found.');
        }

        if (!$this->isUserAssociated($task)) {
            throw $this->createAccessDeniedException('Access Denied.');
        }

        $this->taskRepository->remove($task, true);

        return new Response(
            $id,
            Response::HTTP_OK,
            ['content-type' => 'application/json']
        );
    }

    private function isUserAssociated(Task $task): bool
    {
        $associated = $task->getUserId()->toBinary() === $this->getUser()->getId()->toBinary();
        if (!$associated) {
            $this->logger->alert('Unauthorized access attempt.', [
                'ip' => $this->requestStack->getCurrentRequest()->getClientIp(),
                'user' => $this->getUser()->getUserIdentifier(),
                'task' => $task->getId(),
                'route' => $this->requestStack->getCurrentRequest()->attributes->get('_route'),
            ]);
        }

        return $associated;
    }
}
