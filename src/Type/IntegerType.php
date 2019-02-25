<?php

namespace VahidIrn\FilterQuery\Type;

use VahidIrn\FilterQuery\FilterQuery;
use VahidIrn\FilterQuery\FilterQueryType;

class IntegerType implements FilterQueryType
{
    const type = 'Integer';
    static function defaultRules () {
        return [
          FilterQuery::EQ,
          FilterQuery::IN,
          FilterQuery::MIN,
          FilterQuery::MAX,
          FilterQuery::LT,
          FilterQuery::GT
        ];
    }
}
