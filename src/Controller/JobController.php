<?php

declare(strict_types=1);

namespace App\Controller;

use App\Controller\Traits\IsUserAssociated;
use App\Entity\Job;
use App\Entity\Task;
use App\Form\JobType;
use App\Repository\JobRepository;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Notifier\Notification\Notification;
use Symfony\Component\Notifier\NotifierInterface;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/job', name: 'job_')]
class JobController extends AbstractController
{
    use IsUserAssociated;

    public function __construct(
        private JobRepository $jobRepository,
        private NotifierInterface $notifier,
        private LoggerInterface $logger,
        private RequestStack $requestStack,
    ) {
    }

    #[Route('/editor/{task}/{job?}', name: 'editor', methods: ['GET', 'POST'])]
    public function editor(Request $request, Task $task): Response
    {
        if (!$this->isUserAssociated($task)) {
            return $this->redirectToRoute('tasks_index');
        }

        $job = $request->get('job');
        $isEdit = null !== $job;

        $form = $this->createForm(JobType::class, $this->jobRepository->findOneById($job));

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $job = $form->getData();
            $job->setTask($task);

            $this->jobRepository->save($job, true);

            $this->notifier->send(new Notification(sprintf('Job %s.', $isEdit ? 'edited' : 'created'), ['browser']));
            $this->logger->info('Job saved', [
                'ip' => $request->getClientIp(),
                'job' => $job->getId(),
                'user' => $this->getUser()->getUserIdentifier(),
                'route' => $request->attributes->get('_route'),
            ]);

            return $this->redirectToRoute('tasks_editor', ['task' => $task->getId()]);
        }

        return $this->render('job/editor.html.twig', [
            'form' => $form,
            'is_edit' => $isEdit,
            'task' => $task->getId(),
        ]);
    }

    #[Route('/update/{job}', name: 'update', methods: ['POST'])]
    public function update(Request $request, Job $job): Response
    {
        $task = $job->getTask();
        if (!$this->isUserAssociated($task)) {
            return $this->redirectToRoute('tasks_index');
        }

        $token = $request->request->get('csrf_token');
        if (!$this->isCsrfTokenValid('job_update', $token)) {
            return $this->redirectToRoute('tasks_editor');
        }

        $completed = $request->request->get('is_completed');

        $job->setIsCompleted('on' === $completed);

        $this->jobRepository->save($job, true);

        $delete = $request->request->get('delete');
        if ('on' === $delete) {
            $this->logger->info('Job deleted', [
                'ip' => $request->getClientIp(),
                'job' => $job->getId(),
                'user' => $this->getUser()->getUserIdentifier(),
                'route' => $request->attributes->get('_route'),
            ]);
            $this->jobRepository->remove($job, true);
            $this->notifier->send(new Notification('Job deleted', ['browser']));
        }

        return $this->redirectToRoute('tasks_editor', ['task' => $task->getId()]);
    }
}
