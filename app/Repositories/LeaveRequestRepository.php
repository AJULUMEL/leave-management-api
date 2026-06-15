<?php

namespace App\Repositories;

use App\Models\LeaveRequest;
use Illuminate\Database\Eloquent\Collection;

class LeaveRequestRepository
{
    public function create(array $data): LeaveRequest
    {
        return LeaveRequest::create($data);
    }

    public function getByUserId(int $userId): Collection
    {
        return LeaveRequest::where('user_id', $userId)
            ->latest()
            ->get();
    }

    public function getAll(): Collection
    {
        return LeaveRequest::with(['employee', 'approver', 'rejecter'])
            ->latest()
            ->get();
    }

    public function getUsedLeaveDaysByYear(int $userId, int $year): int
    {
        return (int) LeaveRequest::where('user_id', $userId)
            ->whereYear('start_date', $year)
            ->whereIn('status', ['pending', 'approved'])
            ->sum('total_days');
    }
}