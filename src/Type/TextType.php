<?php

namespace VahidIrn\FilterQuery\Type;

use VahidIrn\FilterQuery\FilterQuery;
use VahidIrn\FilterQuery\FilterQueryType;

class TextType implements FilterQueryType
{
    const type = 'Text';
    static function defaultRules () {
        return [
          FilterQuery::FT,
          FilterQuery::EQ,
          FilterQuery::LIKE,
          FilterQuery::ILIKE,
          FilterQuery::MATCH
        ];
    }
}
