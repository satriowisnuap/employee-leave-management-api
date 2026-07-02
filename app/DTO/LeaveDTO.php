<?php

namespace App\DTO;

readonly class LeaveDTO
{
    public function __construct(
        public readonly int $userId,
        public readonly string $startDate,
        public readonly string $endDate,
        public readonly string $reason,
        public readonly string $attachment,
    ) {}
}
