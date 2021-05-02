<?php


namespace Sparrow;


class Paginate
{
    /**
     * @return int
     */


    public static function getPage(): int
    {
        $page = intval($_GET['page'] ?? '1');
        if ($page < 1)
            $page = 1;
        return $page;
    }

    /**
     * @return string
     */

    public static function queryPaginateLimit(): string
    {
        return ' LIMIT ' . ((self::getPage() - 1) * DEFAULT_PAGE_LIMIT) . ', ' . DEFAULT_PAGE_LIMIT;
    }

    /**
     * @return float|int
     */

    public static function i() {
        return 1 + ((self::getPage() - 1) * DEFAULT_PAGE_LIMIT);
    }
}