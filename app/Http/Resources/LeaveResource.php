<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LeaveResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,

            'employee' => [
                'id' => $this->user->id,
                'name' => $this->user->name,
            ],

            'start_date' => $this->start_date,

            'end_date' => $this->end_date,

            'days' => $this->days,

            'reason' => $this->reason,

            'attachment' => $this->attachment,

            'status' => $this->status,

            'approved_at' => $this->approved_at,

            'created_at' => $this->created_at,
        ];
    }
}
