<?php

namespace VahidIrn\FilterQueryNeo\Type;

use VahidIrn\FilterQueryNeo\FilterQueryNeo;
use VahidIrn\FilterQueryNeo\FilterQueryNeoType;

class IntegerType implements FilterQueryNeoType
{
    const type = 'Integer';
    static function defaultRules () {
        return [
          FilterQueryNeo::EQ,
          FilterQueryNeo::IN,
          FilterQueryNeo::MIN,
          FilterQueryNeo::MAX,
          FilterQueryNeo::LT,
          FilterQueryNeo::GT
        ];
    }
}
