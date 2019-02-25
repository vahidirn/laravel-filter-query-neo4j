<?php

namespace VahidIrn\FilterQuery\Type;

use VahidIrn\FilterQuery\FilterQuery;
use VahidIrn\FilterQuery\FilterQueryType;

class DateType implements FilterQueryType
{
    const type = 'Date';
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
