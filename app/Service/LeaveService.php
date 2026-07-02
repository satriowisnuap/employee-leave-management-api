<?php

namespace App\Service;

use App\DTO\LeaveDTO;
use App\Enums\LeaveStatus;
use App\Interfaces\LeaveRepositoryInterface;
use App\Models\LeaveRequest;
use Carbon\Carbon;
use Exception;
use Illuminate\Database\Eloquent\Collection;

class LeaveService
{
    public function __construct(
        private readonly LeaveRepositoryInterface $leaveRepository,
    ) {}

    public function submitLeave(LeaveDTO $dto): LeaveRequest
    {
        $start = Carbon::parse($dto->startDate);
        $end = Carbon::parse($dto->endDate);

        if ($end->isBefore($start)) {
            throw new Exception('End date must be after or equal to start date.');
        }

        $days = $start->diffInDays($end) + 1;
        $year = $start->year;

        $approvedDays = $this->leaveRepository->getApprovedDaysInYear($dto->userId, $year);
        if (($approvedDays + $days) > 12) {
            throw new Exception('Leave quota exceeded. You have '.(12 - $approvedDays).' days left this year.');
        }

        if ($this->leaveRepository->hasOverlappingLeave($dto->userId, $dto->startDate, $dto->endDate)) {
            throw new Exception('You have an overlapping pending or approved leave request for this period.');
        }

        return $this->leaveRepository->create([
            'user_id' => $dto->userId,
            'start_date' => $dto->startDate,
            'end_date' => $dto->endDate,
            'days' => $days,
            'reason' => $dto->reason,
            'attachment' => $dto->attachment,
            'status' => LeaveStatus::Pending->value,
        ]);
    }

    public function approve(int $leaveId, int $adminId): LeaveRequest
    {
        $leave = $this->leaveRepository->findById($leaveId);
        if (! $leave) {
            throw new Exception('Leave request not found.');
        }

        if ($leave->status !== LeaveStatus::Pending->value) {
            throw new Exception('Only pending leaves can be approved.');
        }

        $year = Carbon::parse($leave->start_date)->year;
        $approvedDays = $this->leaveRepository->getApprovedDaysInYear($leave->user_id, $year);

        if (($approvedDays + $leave->days) > 12) {
            // Auto reject if quota exceeded
            return $this->reject($leaveId, $adminId);
        }

        return $this->leaveRepository->update($leave, [
            'status' => LeaveStatus::Approved->value,
            'approved_by' => $adminId,
            'approved_at' => now(),
        ]);
    }

    public function reject(int $leaveId, int $adminId): LeaveRequest
    {
        $leave = $this->leaveRepository->findById($leaveId);
        if (! $leave) {
            throw new Exception('Leave request not found.');
        }

        if ($leave->status !== LeaveStatus::Pending->value) {
            throw new Exception('Only pending leaves can be rejected.');
        }

        return $this->leaveRepository->update($leave, [
            'status' => LeaveStatus::Rejected->value,
            'approved_by' => $adminId,
            'approved_at' => now(),
        ]);
    }

    public function getEmployeeLeaves(int $userId): Collection
    {
        return $this->leaveRepository->getEmployeeLeaves($userId);
    }

    public function getEmployeeLeave(int $leaveId, int $userId): LeaveRequest
    {
        $leave = $this->leaveRepository->findByIdAndUserId($leaveId, $userId);

        if (! $leave) {
            throw new Exception('Leave request not found.');
        }

        return $leave;
    }

    public function getAllLeaves(): Collection
    {
        return $this->leaveRepository->getAll();
    }
}
