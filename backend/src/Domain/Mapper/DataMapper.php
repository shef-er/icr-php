<?php
declare(strict_types=1);

namespace App\Domain\Mapper;

use App\Db;
use App\Domain\EntityInterface as Entity;

abstract class DataMapper
{
    /**
     * @return Entity[]|false False is returned if no data are found.
     *
     * @throws DBALException
     */
    abstract public function findAll();

    /**
     * @param string $id
     *
     * @return Entity|false False is returned if no data are found.
     *
     * @throws DBALException
     */
    abstract public function findById(string $id);

    /**
     * Hydrate all values from given array.
     * 
     * @param Entity $object
     * @param array $data
     */
    public static function hydrate(Entity &$object, array $data = [], bool $skip_id = false): void
    {
        foreach ($data as $key => $value) {
            if($skip_id && $key == 'id') continue;
            
            /* https://github.com/facebook/hhvm/issues/6368 */
            $key = str_replace("_", " ", $key);
            $key = ucwords($key);
            $method = 'set' . str_replace(" ", "", $key);
            if (method_exists($object, $method)) {
                call_user_func([$object, $method], $value);
            }
        }
    }
}