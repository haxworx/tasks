<?php

declare(strict_types=1);

namespace App\Controller;

use App\Controller\Traits\IsUserAssociated;
use App\Entity\Task;
use App\Form\TaskType;
use App\Repository\TaskRepository;
use Doctrine\Persistence\ManagerRegistry;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Notifier\Notification\Notification;
use Symfony\Component\Notifier\NotifierInterface;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/tasks', name: 'tasks_')]
class TaskController extends AbstractController
{
    use IsUserAssociated;

    public function __construct(
        private TaskRepository $taskRepository,
        private LoggerInterface $logger,
        private RequestStack $requestStack
    ) {
    }

    #[Route('/', name: 'index')]
    public function index(): Response
    {
        $tasks = $this->taskRepository->findAllByUserId($this->getUser()->getId());

        return $this->render('tasks/index.html.twig', [
            'tasks' => $tasks,
        ]);
    }

    #[Route('/editor/{task?}', name: 'editor', methods: ['POST', 'GET'])]
    public function editor(Request $request, NotifierInterface $notifier): Response
    {
        $id = $request->get('task');
        $task = $this->taskRepository->findOneById($id);
        $existsTask = null !== $task;
        if ($existsTask) {
            if (!$this->isUserAssociated($task)) {
                return $this->redirectToRoute('tasks_editor');
            }
        } else {
            $task = new Task();
            $task->setUserId($this->getUser()->getId());
            $task->setTimeStart(new \DateTime());
            $task->setTimeFinish(new \DateTime());
        }

        $form = $this->createForm(TaskType::class, $task);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->taskRepository->save($form->getData(), !$existsTask, true);

            $this->logger->info('Task saved.', [
                'ip' => $request->getClientIp(),
                'user' => $this->getUser()->getUserIdentifier(),
                'task' => $task->getId(),
                'route' => $request->attributes->get('_route'),
            ]);

            $notifier->send(
                new Notification(
                    sprintf('Task %s', !$existsTask ? 'created' : 'updated'),
                    ['browser']
                )
            );

            return $this->redirectToRoute('tasks_editor', [
                'task' => $task->getId(),
            ]);
        }

        $hasJobs = null !== $task->getJobs();

        return $this->render('tasks/editor.html.twig', [
            'form' => $form,
            'is_edit' => $existsTask,
            'task' => $existsTask ? $task->getId() : null,
            'jobs' => $hasJobs ? $task->getJobs() : null,
        ]);
    }

    #[Route('/delete', name: 'delete', methods: ['POST'])]
    public function delete(Request $request, ManagerRegistry $doctrine): Response
    {
        $id = $request->request->get('task');
        $token = $request->request->get('csrf_token');
        if (!$this->isCsrfTokenValid('tasks_delete', $token)) {
            return $this->redirectToRoute('tasks_index');
        }
        $task = $this->taskRepository->findOneById($id);
        if ($task) {
            if ($this->isUserAssociated($task)) {
                $this->logger->info('Task deleted', [
                    'ip' => $request->getClientIp(),
                    'user' => $this->getUser()->getUserIdentifier(),
                    'task' => $task->getId(),
                    'route' => $request->attributes->get('_route'),
                ]);
                $em = $doctrine->getManager();
                $em->remove($task);
                $em->flush();
            }
        }

        return $this->redirectToRoute('tasks_index');
    }

    #[Route('/priority', name: 'priority', methods: ['POST'])]
    public function changePriority(Request $request): Response
    {
        $tasks = $request->request->getIterator();
        foreach ($tasks as $id => $priority) {
            $task = $this->taskRepository->findOneById($id);
            if ($task) {
                if ($this->isUserAssociated($task)) {
                    $value = intval($priority);
                    if ($this->isPriorityValid($value)) {
                        $this->logger->info('Task priority changed.', [
                            'ip' => $request->getClientIp(),
                            'user' => $this->getUser()->getUserIdentifier(),
                            'task' => $task->getId(),
                            'route' => $request->attributes->get('_route'),
                        ]);
                        $task->setPriority($value);
                        $this->taskRepository->save($task, false, true);
                    }
                }
            }
        }

        return $this->redirectToRoute('tasks_index');
    }

    private function isPriorityValid(int $value): bool
    {
        return ($value >= 1) && ($value <= 100);
    }
}
