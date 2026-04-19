<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use Illuminate\Http\Request;

abstract class Controller
{
    protected function logActivity(Request $request, string $action, ?string $subjectType = null, ?int $subjectId = null, ?string $description = null): void
    {
        ActivityLog::create([
            'user_id' => $request->user()?->id,
            'action' => $action,
            'subject_type' => $subjectType,
            'subject_id' => $subjectId,
            'description' => $description,
        ]);
    }
}
