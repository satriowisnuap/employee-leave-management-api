<?php

namespace App\Repositories;

use App\Interfaces\LeaveRepositoryInterface;
use App\Models\LeaveRequest;
use Illuminate\Database\Eloquent\Collection;

class LeaveRepository implements LeaveRepositoryInterface
{
    public function create(array $data): LeaveRequest
    {
        return LeaveRequest::create($data);
    }

    public function update(LeaveRequest $leaveRequest, array $data): LeaveRequest
    {
        $leaveRequest->update($data);

        return $leaveRequest->refresh();
    }

    public function findById(int $id): ?LeaveRequest
    {
        return LeaveRequest::find($id);
    }

    public function getEmployeeLeaves(int $userId): Collection
    {
        return LeaveRequest::where('user_id', $userId)
            ->latest()
            ->get();
    }

    public function getAll(): Collection
    {
        return LeaveRequest::latest()->get();
    }
}
