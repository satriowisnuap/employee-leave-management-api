<?php

namespace App\Service;

use App\Interfaces\LeaveRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class LeaveService
{
    public function __construct(
        private readonly LeaveRepositoryInterface $leaveRepository,
    ) {}

    public function getEmployeeLeaves(int $userId): Collection
    {
        return $this->leaveRepository->getEmployeeLeaves($userId);
    }

    public function getAllLeaves(): Collection
    {
        return $this->leaveRepository->getAll();
    }

}
