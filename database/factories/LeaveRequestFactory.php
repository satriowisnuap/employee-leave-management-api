<?php

namespace Database\Factories;

use App\Enums\LeaveStatus;
use App\Models\LeaveRequest;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<LeaveRequest>
 */
class LeaveRequestFactory extends Factory
{
    protected $model = LeaveRequest::class;

    public function definition(): array
    {
        $start = fake()->dateTimeBetween('now', '+30 days');
        $end = fake()->dateTimeBetween($start, '+35 days');

        $startDate = Carbon::instance($start);
        $endDate = Carbon::instance($end);
        $days = (int) $startDate->diffInDays($endDate) + 1;

        return [
            'user_id' => User::factory(),
            'start_date' => $startDate->toDateString(),
            'end_date' => $endDate->toDateString(),
            'days' => $days,
            'reason' => fake()->sentence(),
            'attachment' => 'attachments/test.pdf',
            'status' => LeaveStatus::Pending->value,
            'approved_by' => null,
            'approved_at' => null,
        ];
    }

    public function pending(): static
    {
        return $this->state(['status' => LeaveStatus::Pending->value]);
    }

    public function approved(): static
    {
        return $this->state([
            'status' => LeaveStatus::Approved->value,
            'approved_at' => now(),
        ]);
    }

    public function rejected(): static
    {
        return $this->state([
            'status' => LeaveStatus::Rejected->value,
            'approved_at' => now(),
        ]);
    }
}
