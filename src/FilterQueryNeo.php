<?php

namespace VahidIrn\FilterQueryNeo;

class FilterQueryNeo
{
    const EQ = 'EQ'; // equal to
    const CONTAINS = 'CONTAINS'; // CONTAINS
    const STARTS_WITH = 'STARTS_WITH'; // Starts with character
    const ENDS_WITH = 'ENDS_WITH'; // Ends with character
    const REGEX = 'REGEX'; // Ends with character
    const MIN = 'MIN'; // greater than or equal to
    const MAX = 'MAX'; // less than or equal to
    const LT = 'LT'; // less than
    const GT = 'GT'; // greater than
    const RE = 'RE'; // regular expression match
    const FT = 'FT'; // full text search
    const IN = 'IN'; // list contains
    const NULL = 'NULL'; // is null
    
    const String = Type\StringType::class;
    const Text = Type\TextType::class;
    const Integer = Type\IntegerType::class;
    const Numeric = Type\NumericType::class;
    const Enum = Type\EnumType::class;
    const Date = Type\DateType::class;
    const Boolean = Type\BooleanType::class;

    static function isFilterQueryNeoType($class) {
        return is_string($class) && class_exists($class) && in_array(FilterQueryNeoType::class, class_implements($class));
    }
}
