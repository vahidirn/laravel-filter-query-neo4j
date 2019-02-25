<?php

namespace VahidIrn\FilterQuery\Type;

use VahidIrn\FilterQuery\FilterQuery;
use VahidIrn\FilterQuery\FilterQueryType;

class NumericType implements FilterQueryType
{
    const type = 'Numeric';
    static function defaultRules () {
        return [
          FilterQuery::EQ,
          FilterQuery::MIN,
          FilterQuery::MAX,
          FilterQuery::LT,
          FilterQuery::GT
        ];
    }
}
