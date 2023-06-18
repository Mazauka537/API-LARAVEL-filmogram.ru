<?php

namespace App\Helpers;

use App\Models\Save;

class LoadableItems {

    public static function getData($itemsCount, $page, $itemsPerPage = 10) {
        $limit = $itemsPerPage;
        $skip = ($page - 1) * $limit;

        $totalPages = ceil($itemsCount / $limit);

        return [$limit, $skip, $totalPages];
    }
}
