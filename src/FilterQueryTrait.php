<?php

namespace VahidIrn\FilterQuery;

use \Illuminate\Support\Facades\DB;
use \Illuminate\Database\Eloquent\Relations;

trait FilterQueryTrait
{
    protected $filterQueryFilter = null;
    
    protected static function boot()
    {
        parent::boot();
        static::addGlobalScope(new FilterQueryScope);
    }
  
    public function getFilterQuery () {
        return isset($this->filterQuery) ? $this->filterQuery : [];
    }
    
    public function getFilterQueryFtTable ($field) {
        return isset($this->filterQueryFtTable) ? $this->filterQueryFtTable : ($this->getTable() . '_filterQuery');
    }
    
    public function getFilterQueryFtKeyName ($field) {
        return isset($this->filterQueryFtKeyName) ? $this->filterQueryFtKeyName : $this->getKeyName();
    }
    
    public function getFilterQueryFtVector ($field) {
        return isset($this->filterQueryFtVector) ? $this->filterQueryFtVector : "${field}_vector";
    }
    
    public function getFilterQueryFilter () {
        return $this->filterQueryFilter;
    }
    
    public function scopeFilter ($query, $args = null)
    {
        if ($args === null) {
            return $query;
        }
        if (isset($this->filterQueryFilter)) {
            $this->filterQueryFilter = $this->filterQueryMergeFilters($this->filterQueryFilter, $args);
        } else {
            $this->filterQueryFilter = $args;
        }
        return $query;
    }
    
    public function scopeFilterApply ($query)
    {
        if (!isset($this->filterQueryFilter)) {
            return $query;
        }
        $filter = $this->filterQueryFilter;
        unset($this->filterQueryFilter);
        return $query->filterQueryApply($filter);
    }
    
    public function scopeFilterQueryApply($query, $args, $root = null)
    {
        if ($args === null) {
            return $query;
        }
        if ($args instanceof $this) {
          return $query->filterQueryModel($args, $root);
        }
        if (isset($args['AND'])) {
            if (count($args) !== 1) {
                throw new FilterQueryException('AND parameter must not have peers');
            }
            return $query->filterQueryAnd($args['AND'], $root);
        }
        if (isset($args['OR'])) {
            if (count($args) !== 1) {
                throw new FilterQueryException('OR parameter must not have peers');
            }
            return $query->filterQueryOr($args['OR'], $root);
        }
        if (isset($args['NOT'])) {
            if (count($args) !== 1) {
                throw new FilterQueryException('NOT parameter must not have peers');
            }
            return $query->filterQueryNot($args['NOT'], $root);
        }
        if (isset($args['NOR'])) {
            if (count($args) !== 1) {
                throw new FilterQueryException('NOR parameter must not have peers');
            }
            return $query->filterQueryNor($args['NOR'], $root);
        }
        foreach ($this->getFilterQuery() as $field => $rules) {
            if ($rules instanceof Relations\Relation) {
                if (array_key_exists($field, $args)) {
                    $query->filterQueryRelation($rules, $field, $args[$field], $root);
                    unset($args[$field]);
                }
                continue;
            }
            $rules = collect($rules)->map(function ($rule) {
                return FilterQuery::isFilterQueryType($rule) ? $rule::defaultRules() : $rule;
            })->flatten()->unique();
            foreach ($rules as $n => $rule) {
                if ($n === 0) {
                    $k = $field;
                    $nk = "${field}_NOT";
                } else {
                    $k = "${field}_${rule}";
                    $nk = "${field}_NOT_${rule}";
                }
                if (array_key_exists($k, $args)) {
                    $method = 'filter' . $rule;
                    $query->$method($field, $args[$k], $root);
                    unset($args[$k]);
                }
                if (array_key_exists($nk, $args)) {
                    $method = 'filterNot' . $rule;
                    $query->$method($field, $args[$nk], $root);
                    unset($args[$nk]);
                }
            }
        }
        if (count($args) > 0) {
            throw new FilterQueryException('Filter query on ' . get_class($this) .' has unknown field: ' . array_keys($args)[0]);
        }
        return $query;
    }
    
    public function scopeFilterEq($query, $field, $arg)
    {
        if ($arg === null) {
            return $query->whereNull($field);
        }
        return $query->where($field, $arg);
    }

    public function scopeFilterNotEq($query, $field, $arg)
    {
        if ($arg === null) {
            return $query->whereNotNull($field);
        }
        return $query->where($field, '<>' ,$arg);
    }


    public function scopeFilterRegex($query, $field, $arg)
    {
        if ($arg === null) {
            throw new FilterQueryException('Start With rule does not accept null"');
        }
        return $query->where($field, '=~', $arg);
    }

    public function scopeFilterStarts_With($query, $field, $arg)
    {
        if ($arg === null) {
            throw new FilterQueryException('Start With rule does not accept null"');
        }
        return $query->where($field, 'STARTS WITH', $arg);
    }


    public function scopeFilterEnds_With($query, $field, $arg)
    {
        if ($arg === null) {
            throw new FilterQueryException('Ends With rule does not accept null"');
        }
        return $query->where($field, 'ENDS WITH', $arg);
    }


    public function scopeFilterContains($query, $field, $arg)
    {
        if ($arg === null) {
            throw new FilterQueryException('Contains rule does not accept null"');
        }
        return $query->where($field, 'CONTAINS', $arg);
    }

    public function scopeFilterMin($query, $field, $arg)
    {
        if ($arg === null) {
            throw new FilterQueryException('MIN rule does not accept null"');
        }
        return $query->where($field, '>=', $arg);
    }

    public function scopeFilterMax($query, $field, $arg)
    {
        if ($arg === null) {
            throw new FilterQueryException('MAX rule does not accept null"');
        }
        return $query->where($field, '<=', $arg);
    }

    public function scopeFilterLt($query, $field, $arg)
    {
        if ($arg === null) {
            throw new FilterQueryException('LT rule does not accept null"');
        }
        return $query->where($field, '<', $arg);
    }
    
    public function scopeFilterGt($query, $field, $arg)
    {
        if ($arg === null) {
            throw new FilterQueryException('GT rule does not accept null"');
        }
        return $query->where($field, '>', $arg);
    }

    public function scopeFilterRe($query, $field, $arg)
    {
        if ($arg === null) {
            throw new FilterQueryException('RE rule does not accept null"');
        }
        return $query->where($field, '~', $arg);
    }
    
    public function scopeFilterNotRe($query, $field, $arg)
    {
        if ($arg === null) {
            throw new FilterQueryException('RE rule does not accept null"');
        }
        return $query->where($field, '!~', $arg);
    }
    
    public function scopeFilterIn($query, $field, $arg)
    {
        if ($arg === null) {
            throw new FilterQueryException('IN rule does not accept null"');
        }
        return $query->whereIn($field, $arg);
    }

    public function scopeFilterNotIn($query, $field, $arg)
    {
        if ($arg === null) {
            throw new FilterQueryException('IN rule does not accept null"');
        }
        return $query->whereNotIn($field, $arg);
    }

    public function scopeFilterFt($query, $field, $arg, $root = null)
    {
        if ($arg === null) {
            throw new FilterQueryException('FT rule does not accept null"');
        }
        $root = $root ?: $query;
        $table = $query->getModel()->getFilterQueryFtTable($field);
        $key = $query->getModel()->getFilterQueryFtKeyName($field);
        $vector = $query->getModel()->getFilterQueryFtVector($field);
        $rank = "${field}_rank";
        $_rank = DB::raw('ts_rank('.$this->filterQuery__wrap($vector).', query) as ' . $this->filterQuery__wrap($rank));
        
        $sub = DB::table($table)->select($key, $_rank);
        $sub->crossJoin(DB::raw('plainto_tsquery(?) query'))->addBinding($arg);

        $where = $this->filterQuery__wrap($vector) . ' @@ ' . $this->filterQuery__wrap('query');
        $sub->whereRaw($where);
        
        $t2 = $this->filterQuery__newAlias($field);
        $f1 = $query->getModel()->getQualifiedKeyName();
        $f2 = "${t2}.{$key}";
        $joinMethod = ($root === $query ? 'join' : 'leftJoin');
        $root->$joinMethod(DB::raw("({$sub->toSql()}) as {$this->filterQuery__wrap($t2)}"), $f1, '=', $f2);
        foreach ($sub->getBindings() as $binding) {
            $root->addBinding($binding, 'join');
        }
        if ($joinMethod === 'leftJoin') {
            $query->whereNotNull($f2);
        }
        return $query;
    }

    public function scopeFilterNull($query, $field, $arg = true)
    {
        if ($arg) {
            return $query->whereNull($field);
        } else {
            return $query->whereNotNull($field);
        }
    }
    
    public function scopeFilterNotNull($query, $field, $arg = true)
    {
        return $query->filterNull($field, !$arg);
    }
    
    public function scopeFilterQueryAnd($query, $filters, $root = null)
    {
        $root = $root ?: $query;
        return $query->where(function ($subQuery) use ($filters, $root) {
            foreach($filters as $filter) {
                $subQuery->filterQueryApply($filter, $root);
            }
        });
    }
    
    public function scopeFilterQueryOr($query, $filters, $root = null)
    {
        $root = $root ?: $query;
        return $query->where(function ($subQuery) use ($filters, $root) {
            foreach($filters as $filter) {
                $subQuery->orWhere(function ($subQuery) use ($filter, $root) {
                    $subQuery->filterQueryApply($filter, $root);
                });
            }
        }, null, null, 'or');
    }
    
    public function scopeFilterQueryNot($query, $filters, $root = null)
    {
        $root = $root ?: $query;
        return $query->where(function ($subQuery) use ($filters, $root) {
            foreach($filters as $filter) {
                $subQuery->filterQueryApply($filter, $root);
            }
        }, null, null, 'and not');
    }

    public function scopeFilterQueryNor($query, $filters, $root = null)
    {
        $root = $root ?: $query;
        return $query->where(function ($subQuery) use ($filters, $root) {
            foreach($filters as $filter) {
                $subQuery->orWhere(function ($subQuery) use ($filter, $root) {
                    $subQuery->filterQueryApply($filter, $root);
                });
            }
        }, null, null, 'or not');
    }    

    public function scopeFilterQueryRelation($query, $relation, $field, $args, $root = null)
    {
        $root = $root ?: $query;
        $t1 = $query->getModel()->getTable();
        $f1 = $query->getModel()->getQualifiedKeyName();
        $class = $relation->getRelated();
        $sub = $class::filterQueryApply($args);
        $t2 = $this->filterQuery__newAlias(str_plural($field));
        $joinMethod = ($root === $query ? 'join' : 'leftJoin');
        if ($relation instanceof Relations\BelongsToMany) {
            $sub->join($relation->getTable(), $class->getQualifiedKeyName(), '=', $relation->getOtherKey());
            $a2 = str_singular($t1) . '_id';
            $sub->distinct()->select($relation->getForeignKey() . " as $a2");
            $f2 = $t2 . '.' . $a2;
            $root->$joinMethod(DB::raw("({$sub->toSql()}) as {$this->filterQuery__wrap($t2)}"), $f1, '=', $f2);
        } else if ($relation instanceof Relations\HasOneOrMany) {
            $sub->distinct()->select($relation->getForeignKey());
            $f2 = $t2 . '.' . $relation->getPlainForeignKey();
            $root->$joinMethod(DB::raw("({$sub->toSql()}) as {$this->filterQuery__wrap($t2)}"), $f1, '=', $f2);
        } else {
            $a2 = $field . '_id';
            $sub->distinct()->select($relation->getQualifiedOtherKeyName() . " as $a2");
            $key = $relation->getForeignKey();
            $f1 = $relation->getQualifiedForeignKey();
            $f2 = "$t2.$a2";
            $root->$joinMethod(DB::raw("({$sub->toSql()}) as {$this->filterQuery__wrap($t2)}"), $f1, '=', $f2);
        }
        foreach ($sub->toBase()->getBindings() as $binding) {
            $root->addBinding($binding, 'join');
        }
        if ($joinMethod === 'leftJoin') {
            $query->whereNotNull($f2);
        }
        return $query;
    }
    
    public function scopeFilterQueryModel($query, $model, $root = null)
    {
        $keyName = $query->getModel()->getKeyName();
        return $query->where($keyName, $model->{$keyName});
    }
    
    public function filterQueryMergeFilters ($a, $b)
    {
        $logical = function ($a, $b, $op, $inv_op) {
            $logicOperators = ['AND', 'OR'];
            if (in_array(key($a), $logicOperators)) {
                return [key($a) => (
                    collect(current($a))->map(function ($item) use ($b) {
                        return $this->filterQueryMergeFilters($item, $b);
                    })->toArray()
                )];
            }
            if (key($a) !== $op) {
                return null;
            }
            if (count(current($a)) === 0) {
                return $b;
            }
            $filter = $this->filterQueryMergeFilters([$inv_op => (
                collect(current($a))->map(function ($item) {
                    return ['!' => $item];
                })->toArray())], $b);
            return count(current($filter)) === 1 ? current($filter)[0] : $filter;
        };
        $filter = $logical($a, $b, 'NOT', 'OR') ?: $logical($a, $b, 'NOR', 'AND')
               ?: $logical($b, $a, 'NOT', 'OR') ?: $logical($b, $a, 'NOR', 'AND');
        if ($filter !== null) {
            return $filter;
        }

        $a_inv = key($a) === '!';
        $b_inv = key($b) === '!';
        if ($a_inv) {
            $a = current($a);
        }
        if ($b_inv) {
            $b = current($b);
        }
        
        $filter = [];
        $and = [];
        foreach ($this->getFilterQuery() as $field => $rules) {
            if ($rules instanceof Relations\Relation) {
                $in_a = array_key_exists($field, $a);
                $in_b = array_key_exists($field, $b);
                if ($in_a && $in_b) {
                    $related = $rules->getRelated();
                    if (($a[$field] instanceof $related) || ($b[$field] instanceof $related)) {
                        $and[] = $a_inv ? ['NOT' => [[$field => $a[$field]]]] : [$field => $a[$field]];
                        $and[] = $b_inv ? ['NOT' => [[$field => $b[$field]]]] : [$field => $b[$field]];
                    } else {
                        $a_field = $a_inv ? ['!' => $a[$field]] : $a[$field];
                        $b_field = $b_inv ? ['!' => $b[$field]] : $b[$field];
                        $filter[$field] = $related->filterQueryMergeFilters($a_field, $b_field);
                    }
                } else if ($in_a) {
                    if ($a_inv) {
                        $and[] = ['NOT' => [[$field => $a[$field]]]];
                    } else {
                        $filter[$field] = $a[$field];
                    }
                } else if ($in_b) {
                    if ($b_inv) {
                        $and[] = ['NOT' => [[$field => $b[$field]]]];
                    } else {
                        $filter[$field] = $b[$field];
                    }
                }
                unset($a[$field]);
                unset($b[$field]);
            } else {
                $rules = collect($rules)->map(function ($rule) {
                    return FilterQuery::isFilterQueryType($rule) ? $rule::defaultRules() : $rule;
                })->flatten()->unique();
                foreach ($rules as $n => $rule) {
                    if ($n === 0) {
                        $k = $field;
                    } else {
                        $k = "${field}_${rule}";
                    }
                    $in_a = array_key_exists($k, $a);
                    $in_b = array_key_exists($k, $b);
                    if ($in_a && $in_b) {
                        $and[] = $a_inv ? ['NOT' => [[$k => $a[$k]]]] : [$k => $a[$k]];
                        $and[] = $b_inv ? ['NOT' => [[$k => $b[$k]]]] : [$k => $b[$k]];
                    } else if ($in_a) {
                        if ($a_inv) {
                            $and[] = ['NOT' => [[$k => $a[$k]]]];
                        } else {
                            $filter[$k] = $a[$k];
                        }
                    } else if ($in_b) {
                        if ($b_inv) {
                            $and[] = ['NOT' => [[$k => $b[$k]]]];
                        } else {
                            $filter[$k] = $b[$k];
                        }
                    }
                    unset($a[$k]);
                    unset($b[$k]);
                }
                foreach ($rules as $n => $rule) {
                    if ($n === 0) {
                        $k = "${field}_NOT";
                    } else {
                        $k = "${field}_NOT_${rule}";
                    }
                    $in_a = array_key_exists($k, $a);
                    $in_b = array_key_exists($k, $b);
                    if ($in_a && $in_b) {
                        $and[] = $a_inv ? ['NOT' => [[$k => $a[$k]]]] : [$k => $a[$k]];
                        $and[] = $b_inv ? ['NOT' => [[$k => $b[$k]]]] : [$k => $b[$k]];
                    } else if ($in_a) {
                        if ($a_inv) {
                            $and[] = ['NOT' => [[$k => $a[$k]]]];
                        } else {
                            $filter[$k] = $a[$k];
                        }
                    } else if ($in_b) {
                        if ($b_inv) {
                            $and[] = ['NOT' => [[$k => $b[$k]]]];
                        } else {
                            $filter[$k] = $b[$k];
                        }
                    }
                    unset($a[$k]);
                    unset($b[$k]);
                }
            }
        }
        if (count($a) > 0) {
            throw new FilterQueryException ('Argument #1 Filter query on ' . get_class($this) .' has unknonwn field: ' . collect($a)->keys()[0]);
        }
        if (count($b) > 0) {
            throw new FilterQueryException ('Argument #2 Filter query on ' . get_class($this) .' has unknonwn field: ' . collect($b)->keys()[0]);
        }
        if (count($and) > 0) {
            if (count($filter) > 0) {
                $and[] = $filter;
            }
            return [
                'AND' => $and
            ];
        }
        return $filter;
    }

    private $filterQuery__aliasCount = 0;
    private function filterQuery__newAlias($prefix = 't') {
        return $prefix . '_' . (++$this->filterQuery__aliasCount);
    }
    
    private function filterQuery__wrap($field) {
        return DB::getQueryGrammar()->wrap($field);
    }
}
