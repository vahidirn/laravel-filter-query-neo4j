<?php

namespace VahidIrn\FilterQueryNeo\Type;

use VahidIrn\FilterQueryNeo\FilterQueryNeo;
use VahidIrn\FilterQueryNeo\FilterQueryNeoType;

class DateType implements FilterQueryNeoType
{
    const type = 'Date';
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
