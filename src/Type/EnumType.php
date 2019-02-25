<?php

namespace VahidIrn\FilterQuery\Type;

use VahidIrn\FilterQuery\FilterQuery;
use VahidIrn\FilterQuery\FilterQueryType;

class EnumType implements FilterQueryType
{
    const type = 'Enum';
    static function defaultRules () {
        return [
          FilterQuery::EQ,
          FilterQuery::IN
        ];
    }
}
