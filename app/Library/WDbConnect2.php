<?php

namespace App\Library;

use WLib\Db\WDbConnect;

class WDbConnect2 extends WDbConnect
{
    /**
     * @param string $table
     * @param array $data
     * @return int
     */
    public function insertInto(string $table, array $data): int
    {
        return $this->insertCmd($table, $data, 'INSERT INTO');
    }
}