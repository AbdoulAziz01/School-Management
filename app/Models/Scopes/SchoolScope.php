<?php

namespace App\Models\Scopes;

use App\Support\TenantSchool;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class SchoolScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        if (! TenantSchool::shouldFilter()) {
            return;
        }

        $schoolId = TenantSchool::id();

        if ($schoolId) {
            $builder->where($model->getTable().'.school_id', $schoolId);
        }
    }
}
