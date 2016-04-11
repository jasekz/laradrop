<?php
namespace Jasekz\Laradrop\Services;

class Base  {

    /**
     * These will be excluded from where clauses
     * 
     * @var array
     */
    protected  $excludeFilters = [
            'sortby',
            'dir',
            'page',
            'ids',
            'showEntries',
        ];
}