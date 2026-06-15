<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreLeaveRequestRequest;
use App\Models\LeaveRequest;
use App\Services\LeaveRequestService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LeaveRequestController extends Controller
{
    public function __construct(
        protected LeaveRequestService $leaveRequestService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $leaveRequests = $this->leaveRequestService
            ->getMyLeaveRequests($request->user());

        return response()->json([
            'message' => 'My leave requests retrieved successfully.',
            'data' => $leaveRequests,
        ]);
    }

    public function store(StoreLeaveRequestRequest $request): JsonResponse
    {
        $leaveRequest = $this->leaveRequestService
            ->createLeaveRequest($request->user(), $request->validated());

        return response()->json([
            'message' => 'Leave request submitted successfully.',
            'data' => $leaveRequest,
        ], 201);
    }

    public function show(Request $request, LeaveRequest $leaveRequest): JsonResponse
    {
        if ($leaveRequest->user_id !== $request->user()->id) {
            return response()->json([
                'message' => 'Forbidden. You can only view your own leave request.',
            ], 403);
        }

        return response()->json([
            'message' => 'Leave request retrieved successfully.',
            'data' => $leaveRequest,
        ]);
    }
}