<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\RejectLeaveRequestRequest;
use App\Models\LeaveRequest;
use App\Services\LeaveRequestService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminLeaveRequestController extends Controller
{
    public function __construct(
        protected LeaveRequestService $leaveRequestService
    ) {}

    public function index(): JsonResponse
    {
        $leaveRequests = $this->leaveRequestService->getAllLeaveRequests();

        return response()->json([
            'message' => 'All leave requests retrieved successfully.',
            'data' => $leaveRequests,
        ]);
    }

    public function show(LeaveRequest $leaveRequest): JsonResponse
    {
        return response()->json([
            'message' => 'Leave request detail retrieved successfully.',
            'data' => $leaveRequest->load(['employee', 'approver', 'rejecter']),
        ]);
    }

    public function approve(Request $request, LeaveRequest $leaveRequest): JsonResponse
    {
        $leaveRequest = $this->leaveRequestService
            ->approve($leaveRequest, $request->user());

        return response()->json([
            'message' => 'Leave request approved successfully.',
            'data' => $leaveRequest,
        ]);
    }

    public function reject(
        RejectLeaveRequestRequest $request,
        LeaveRequest $leaveRequest
    ): JsonResponse {
        $leaveRequest = $this->leaveRequestService
            ->reject(
                $leaveRequest,
                $request->user(),
                $request->validated()['admin_note'] ?? null
            );

        return response()->json([
            'message' => 'Leave request rejected successfully.',
            'data' => $leaveRequest,
        ]);
    }
}