<?php

namespace LEXO\PO;

use const LEXO\PO\{
    CACHE_KEY
};

class Deactivation
{
    public static function run()
    {
        delete_transient(CACHE_KEY);
    }
}
