<?php

namespace VahidIrn\FilterQueryNeo\Type;

use VahidIrn\FilterQueryNeo\FilterQueryNeo;
use VahidIrn\FilterQueryNeo\FilterQueryNeoType;

class EnumType implements FilterQueryNeoType
{
    const type = 'Enum';
    static function defaultRules () {
        return [
          FilterQueryNeo::EQ,
          FilterQueryNeo::IN
        ];
    }
}
