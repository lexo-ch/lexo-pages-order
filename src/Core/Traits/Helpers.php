<?php

namespace LEXO\PO\Core\Traits;

use LEXO\PO\Core\Notices\Notice;
use LEXO\PO\Core\Notices\Notices;

trait Helpers
{
    public $notice;
    public $notices;

    public function __construct()
    {
        $this->notice = new Notice();
        $this->notices = new Notices();
    }

    public static function getClassName(string $classname): string
    {
        if ($name = strrpos($classname, '\\')) {
            return substr($classname, $name + 1);
        };

        return $name;
    }

    public static function setStatus404(): void
    {
        global $wp_query;
        $wp_query->set_404();
        status_header(404);
        nocache_headers();
    }

    public static function printr(mixed $data): string
    {
        return "<pre>" . \print_r($data, true) . "</pre>";
    }
}
