<?php

namespace VahidIrn\FilterQueryNeo\Type;

use VahidIrn\FilterQueryNeo\FilterQueryNeo;
use VahidIrn\FilterQueryNeo\FilterQueryNeoType;

class BooleanType implements FilterQueryNeoType
{
    const type = 'Boolean';
    static function defaultRules () {
        return [
          FilterQueryNeo::EQ
        ];
    }
}
