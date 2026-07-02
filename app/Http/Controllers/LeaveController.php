<?php

namespace App\Http\Controllers;

use App\DTO\LeaveDTO;
use App\Helpers\ApiResponse;
use App\Http\Requests\StoreLeaveRequest;
use App\Service\LeaveService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LeaveController extends Controller
{
    public function __construct(
        private readonly LeaveService $leaveService
    ) {}

    /**
     * Employee: list own leave requests.
     * Admin: list all leave requests (redirected to AdminLeaveController).
     */
    public function index(Request $request): JsonResponse
    {
        $leaves = $this->leaveService->getEmployeeLeaves($request->user()->id);

        return ApiResponse::success($leaves);
    }

    /**
     * Employee: show a single own leave request.
     */
    public function show(Request $request, int $id): JsonResponse
    {
        try {
            $leave = $this->leaveService->getEmployeeLeave($id, $request->user()->id);

            return ApiResponse::success($leave);
        } catch (Exception $e) {
            return ApiResponse::error($e->getMessage(), 404);
        }
    }

    /**
     * Employee: submit a new leave request.
     */
    public function store(StoreLeaveRequest $request): JsonResponse
    {
        try {
            $path = $request->file('attachment')->store('attachments', 'public');

            $dto = new LeaveDTO(
                userId: $request->user()->id,
                startDate: $request->validated('start_date'),
                endDate: $request->validated('end_date'),
                reason: $request->validated('reason'),
                attachment: $path
            );

            $leave = $this->leaveService->submitLeave($dto);

            return ApiResponse::success($leave, 'Leave request submitted successfully', 201);
        } catch (Exception $e) {
            return ApiResponse::error($e->getMessage());
        }
    }
}
