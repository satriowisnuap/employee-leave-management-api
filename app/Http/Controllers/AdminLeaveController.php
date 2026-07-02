<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Service\LeaveService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Exception;

class AdminLeaveController extends Controller
{
    public function __construct(
        private readonly LeaveService $leaveService
    ) {}

    /**
     * Admin: list all leave requests.
     */
    public function index(): JsonResponse
    {
        $leaves = $this->leaveService->getAllLeaves();

        return ApiResponse::success($leaves);
    }

    /**
     * Admin: approve a leave request.
     */
    public function approve(Request $request, int $id): JsonResponse
    {
        try {
            $leave = $this->leaveService->approve($id, $request->user()->id);

            return ApiResponse::success($leave, 'Leave approved successfully');
        } catch (Exception $e) {
            return ApiResponse::error($e->getMessage());
        }
    }

    /**
     * Admin: reject a leave request.
     */
    public function reject(Request $request, int $id): JsonResponse
    {
        try {
            $leave = $this->leaveService->reject($id, $request->user()->id);

            return ApiResponse::success($leave, 'Leave rejected successfully');
        } catch (Exception $e) {
            return ApiResponse::error($e->getMessage());
        }
    }
}
