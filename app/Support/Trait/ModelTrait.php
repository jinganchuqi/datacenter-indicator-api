<?php

namespace App\Support\Trait;


use Hyperf\Database\Model\Builder;

trait ModelTrait
{
    /**
     * 根据fillable字段带select查询
     * @return Builder
     */
    public static function fillableQuery(): Builder
    {
        $query = self::query();
        $static = new static;
        $column = $static->fillable;
        if (!empty($column)) {
            $id = !empty($static->primaryKey) ? $static->primaryKey : "id";
            array_unshift($column, $id);
            $query->select($column);
        }

        return $query;
    }


    /**
     * 查询fillable字段
     * @return array|string
     */
    public static function fillableColumn(): array|string
    {
        $static = new static;
        $column = $static->fillable;
        if (!empty($column)) {
            $id = !empty($static->primaryKey) ? $static->primaryKey : "id";
            array_unshift($column, $id);
            return $column;
        }
        return '*';
    }
}
