<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\TaskRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Types\UlidType;
use Symfony\Component\Uid\Ulid;

#[ORM\Entity(repositoryClass: TaskRepository::class)]
class Task
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\Column(type: UlidType::NAME, unique: true)]
    #[ORM\CustomIdGenerator(class: 'doctrine.ulid_generator')]
    private Ulid $id;

    #[ORM\Column(type: UlidType::NAME)]
    private Ulid $userId;

    #[ORM\Column(length: 255)]
    private string $title;

    #[ORM\Column(type: Types::TEXT)]
    private string $description;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private \DateTimeInterface $timeStart;

    #[ORM\Column(type: TYPES::DATETIME_MUTABLE)]
    private \DateTimeInterface $timeFinish;

    #[ORM\Column(type: TYPES::DATETIME_IMMUTABLE, nullable: true)]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $notes = null;

    #[ORM\Column]
    private bool $isCompleted = false;

    #[ORM\Column]
    private bool $inProgress = false;

    #[ORM\Column]
    private int $priority = 1;

    #[ORM\OneToMany(mappedBy: 'task', targetEntity: Job::class, orphanRemoval: true)]
    private Collection $jobs;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->jobs = new ArrayCollection();
    }

    public function getId(): Ulid
    {
        return $this->id;
    }

    public function getUserId(): ?Ulid
    {
        return $this->userId;
    }

    public function setUserId(Ulid $userId): static
    {
        $this->userId = $userId;

        return $this;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getTimeStart(): \DateTimeInterface
    {
        return $this->timeStart;
    }

    public function setTimeStart(\DateTimeInterface $timeStart): static
    {
        $this->timeStart = $timeStart;

        return $this;
    }

    public function getTimeFinish(): \DateTimeInterface
    {
        return $this->timeFinish;
    }

    public function setTimeFinish(\DateTimeInterface $timeFinish): static
    {
        $this->timeFinish = $timeFinish;

        return $this;
    }

    public function getNotes(): ?string
    {
        return $this->notes;
    }

    public function setNotes(?string $notes): static
    {
        $this->notes = $notes;

        return $this;
    }

    public function isCompleted(): bool
    {
        return $this->isCompleted;
    }

    public function setIsCompleted(bool $isCompleted): static
    {
        $this->isCompleted = $isCompleted;

        return $this;
    }

    public function isInProgress(): bool
    {
        return $this->inProgress;
    }

    public function setInProgress(bool $inProgress): static
    {
        $this->inProgress = $inProgress;

        return $this;
    }

    public function getPriority(): int
    {
        return $this->priority;
    }

    public function setPriority(int $priority): static
    {
        $this->priority = $priority;

        return $this;
    }

    public function isLate(): bool
    {
        return $this->timeFinish < new \DateTime();
    }

    /**
     * @return Collection<int, Job>
     */
    public function getJobs(): Collection
    {
        return $this->jobs;
    }

    public function addJob(Job $job): static
    {
        if (!$this->jobs->contains($job)) {
            $this->jobs->add($job);
            $job->setTask($this);
        }

        return $this;
    }

    public function removeJob(Job $job): static
    {
        if ($this->jobs->removeElement($job)) {
            // set the owning side to null (unless already changed)
            if ($job->getTask() === $this) {
                $job->setTask(null);
            }
        }

        return $this;
    }
}
