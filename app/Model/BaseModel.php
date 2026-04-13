<?php

declare(strict_types=1);

namespace App\Model;

use App\Support\Trait\ModelTrait;
use Hyperf\Database\Query\Builder;
use Hyperf\DbConnection\Db;
use Hyperf\DbConnection\Model\Model;

abstract class BaseModel extends Model
{
    use ModelTrait;

    public bool $timestamps = false;


    protected array $fillable = [];

    /**
     * @return Builder
     */
    public function builder(): Builder
    {
        return Db::table($this->getTable());
    }
}
