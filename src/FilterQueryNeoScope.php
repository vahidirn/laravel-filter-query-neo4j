<?php

namespace VahidIrn\FilterQueryNeo;

use Illuminate\Database\Eloquent\Scope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class FilterQueryNeoScope implements Scope
{
    public function apply(Builder $builder, Model $model)
    {
        $builder->filterQueryNeoApply($model->getFilterQueryNeoFilter());
    }
}
