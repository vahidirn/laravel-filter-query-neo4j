<?php

namespace VahidIrn\FilterQueryNeo\Type;

use VahidIrn\FilterQueryNeo\FilterQueryNeo;
use \VahidIrn\FilterQueryNeo\FilterQueryNeoType;

class StringType implements FilterQueryNeoType
{
    const type = 'String';
    static function defaultRules () {
        return [
          FilterQueryNeo::EQ,
          FilterQueryNeo::REGEX,
          FilterQueryNeo::CONTAINS,
          FilterQueryNeo::STARTS_WITH,
          FilterQueryNeo::ENDS_WITH,
        ];
    }
}
