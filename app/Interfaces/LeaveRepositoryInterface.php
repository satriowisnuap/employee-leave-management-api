<?php

namespace App\Interfaces;

use App\Models\LeaveRequest;
use Illuminate\Database\Eloquent\Collection;

interface LeaveRepositoryInterface
{
    public function create(array $data): LeaveRequest;

    public function update(LeaveRequest $leaveRequest, array $data): LeaveRequest;

    public function findById(int $id): ?LeaveRequest;

    public function getEmployeeLeaves(int $userId): Collection;

    public function getAll(): Collection;
}
