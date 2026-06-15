<?php

namespace App\Services;

use App\Models\LeaveRequest;
use App\Models\User;
use App\Repositories\LeaveRequestRepository;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;
use Illuminate\Validation\ValidationException;

class LeaveRequestService
{
    private const ANNUAL_LEAVE_LIMIT = 12;

    public function __construct(
        protected LeaveRequestRepository $leaveRequestRepository
    ) {}

    public function createLeaveRequest(User $user, array $data): LeaveRequest
    {
        $startDate = Carbon::parse($data['start_date']);
        $endDate = Carbon::parse($data['end_date']);

        $totalDays = (int) $startDate->diffInDays($endDate) + 1;
        $year = (int) $startDate->format('Y');

        $usedDays = $this->leaveRequestRepository
            ->getUsedLeaveDaysByYear($user->id, $year);

        if (($usedDays + $totalDays) > self::ANNUAL_LEAVE_LIMIT) {
            throw ValidationException::withMessages([
                'leave_quota' => [
                    'Leave quota exceeded. Maximum leave quota is 12 days per year.',
                ],
            ]);
        }

        /** @var UploadedFile $attachment */
        $attachment = $data['attachment'];
        $attachmentPath = $attachment->store('leave-attachments', 'public');

        return $this->leaveRequestRepository->create([
            'user_id' => $user->id,
            'start_date' => $data['start_date'],
            'end_date' => $data['end_date'],
            'total_days' => $totalDays,
            'reason' => $data['reason'],
            'attachment_path' => $attachmentPath,
            'status' => 'pending',
        ]);
    }

    public function getMyLeaveRequests(User $user): Collection
    {
        return $this->leaveRequestRepository->getByUserId($user->id);
    }

    public function getAllLeaveRequests(): Collection
    {
        return $this->leaveRequestRepository->getAll();
    }

    public function approve(LeaveRequest $leaveRequest, User $admin): LeaveRequest
    {
        if ($leaveRequest->status !== 'pending') {
            throw ValidationException::withMessages([
                'status' => ['Only pending leave requests can be approved.'],
            ]);
        }

        $leaveRequest->update([
            'status' => 'approved',
            'approved_by' => $admin->id,
            'approved_at' => now(),
        ]);

        return $leaveRequest->fresh(['employee', 'approver']);
    }

    public function reject(LeaveRequest $leaveRequest, User $admin, ?string $adminNote = null): LeaveRequest
    {
        if ($leaveRequest->status !== 'pending') {
            throw ValidationException::withMessages([
                'status' => ['Only pending leave requests can be rejected.'],
            ]);
        }

        $leaveRequest->update([
            'status' => 'rejected',
            'rejected_by' => $admin->id,
            'rejected_at' => now(),
            'admin_note' => $adminNote,
        ]);

        return $leaveRequest->fresh(['employee', 'rejecter']);
    }
}