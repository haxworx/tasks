<?php

declare(strict_types=1);

namespace App\Dto;

class UserConfig
{
    private string $oldPassword;
    private string $plainPassword;
    private string $confirmPassword;

    public function __construct()
    {
    }

    public function getOldPassword(): string
    {
        return $this->oldPassword;
    }

    public function getPlainPassword(): string
    {
        return $this->plainPassword;
    }

    public function getConfirmPassword(): string
    {
        return $this->confirmPassword;
    }

    public function setOldPassword(string $oldPassword): static
    {
        $this->oldPassword = $oldPassword;

        return $this;
    }

    public function setPlainPassword(string $plainPassword): static
    {
        $this->plainPassword = $plainPassword;

        return $this;
    }

    public function setConfirmPassword(string $confirmPassword): static
    {
        $this->confirmPassword = $confirmPassword;

        return $this;
    }
}
