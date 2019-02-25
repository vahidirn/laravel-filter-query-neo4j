<?php

namespace VahidIrn\FilterQuery;

class FilterQueryException extends \Exception
{
    public function __construct($message) {
        parent::__construct($message);
    }
}
