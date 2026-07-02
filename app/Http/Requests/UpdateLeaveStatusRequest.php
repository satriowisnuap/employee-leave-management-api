<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateLeaveStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        // Status is part of URL generally for approve/reject or it can be a body param.
        // The business rule says: admin approve/reject.
        // If we use separate endpoints like PATCH /leaves/{id}/approve, we might not need body validation.
        // If we use PATCH /leaves/{id} with body {status: 'approved'}, we need this.
        return [];
    }
}
