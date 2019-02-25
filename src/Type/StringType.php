<?php

namespace VahidIrn\FilterQuery\Type;

use VahidIrn\FilterQuery\FilterQuery;
use \VahidIrn\FilterQuery\FilterQueryType;

class StringType implements FilterQueryType
{
    const type = 'String';
    static function defaultRules () {
        return [
          FilterQuery::EQ,
          FilterQuery::REGEX,
          FilterQuery::CONTAINS,
          FilterQuery::STARTS_WITH,
          FilterQuery::ENDS_WITH,
        ];
    }
}
