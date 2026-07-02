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

    public function hasOverlappingLeave(int $userId, string $startDate, string $endDate): bool
    {
        return LeaveRequest::where('user_id', $userId)
            ->whereIn('status', ['pending', 'approved'])
            ->where(function ($query) use ($startDate, $endDate) {
                $query->whereBetween('start_date', [$startDate, $endDate])
                    ->orWhereBetween('end_date', [$startDate, $endDate])
                    ->orWhere(function ($q) use ($startDate, $endDate) {
                        $q->where('start_date', '<=', $startDate)
                          ->where('end_date', '>=', $endDate);
                    });
            })
            ->exists();
    }

    public function getApprovedDaysInYear(int $userId, int $year): int
    {
        return (int) LeaveRequest::where('user_id', $userId)
            ->where('status', 'approved')
            ->whereYear('start_date', $year)
            ->sum('days');
    }

    public function findByIdAndUserId(int $id, int $userId): ?LeaveRequest
    {
        return LeaveRequest::where('id', $id)
            ->where('user_id', $userId)
            ->first();
    }
}
