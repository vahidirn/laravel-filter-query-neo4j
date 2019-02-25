<?php

namespace VahidIrn\FilterQuery\Type;

use VahidIrn\FilterQuery\FilterQuery;
use VahidIrn\FilterQuery\FilterQueryType;

class BooleanType implements FilterQueryType
{
    const type = 'Boolean';
    static function defaultRules () {
        return [
          FilterQuery::EQ
        ];
    }
}
