<?php

namespace VahidIrn\FilterQueryNeo\Type;

use VahidIrn\FilterQueryNeo\FilterQueryNeo;
use VahidIrn\FilterQueryNeo\FilterQueryNeoType;

class TextType implements FilterQueryNeoType
{
    const type = 'Text';
    static function defaultRules () {
        return [
          FilterQueryNeo::FT,
          FilterQueryNeo::EQ,
          FilterQueryNeo::LIKE,
          FilterQueryNeo::ILIKE,
          FilterQueryNeo::MATCH
        ];
    }
}
