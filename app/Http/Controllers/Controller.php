<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use Illuminate\Database\Eloquent\Builder;
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

    protected function scopeByDesa(Request $request, Builder $query, string $column = 'desa_id'): Builder
    {
        $user = $request->user();

        if ($user?->isRoot()) {
            return $query;
        }

        return $query->where($column, $user?->desa_id);
    }

    protected function abortUnlessCanAccessDesa(Request $request, int|string|null $desaId): void
    {
        $user = $request->user();

        if (! $user || ! $user->canAccessVillage($desaId)) {
            abort(403, 'Anda tidak boleh mengakses data desa lain.');
        }
    }
}
