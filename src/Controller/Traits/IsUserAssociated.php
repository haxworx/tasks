<?php

declare(strict_types=1);

namespace App\Controller\Traits;

use App\Entity\Task;

trait IsUserAssociated
{
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
