<?php

namespace VahidIrn\FilterQueryNeo\Type;

use VahidIrn\FilterQueryNeo\FilterQueryNeo;
use VahidIrn\FilterQueryNeo\FilterQueryNeoType;

class NumericType implements FilterQueryNeoType
{
    const type = 'Numeric';
    static function defaultRules () {
        return [
          FilterQueryNeo::EQ,
          FilterQueryNeo::MIN,
          FilterQueryNeo::MAX,
          FilterQueryNeo::LT,
          FilterQueryNeo::GT
        ];
    }
}
