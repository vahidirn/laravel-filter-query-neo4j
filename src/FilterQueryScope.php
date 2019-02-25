<?php

namespace VahidIrn\FilterQuery;

use Illuminate\Database\Eloquent\Scope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class FilterQueryScope implements Scope
{
    public function apply(Builder $builder, Model $model)
    {
        $builder->filterQueryApply($model->getFilterQueryFilter());
    }
}
