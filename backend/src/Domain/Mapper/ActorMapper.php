<?php
declare(strict_types=1);

namespace App\Domain\Mapper;

use App\Db;
use App\Domain\Entity\Actor;

class ActorMapper extends DataMapper
{
    /**
     * @return Actor[]|false False is returned if no data are found.
     *
     * @throws DBALException
     */
    public function findAll()
    {
        $rows = Db::getInstance()->fetchAll(
            'SELECT * FROM '. Db::ACTORS
        );

        if(!$rows) {
            return false;
        }

        $actors = [];
        foreach ($rows as $row) {
            $actor = new Actor();
            self::hydrate($actor, $row);
            $actors[] = $actor;
        }

        return $actors;
    }

    /**
     * @param string $id
     *
     * @return Actor|false False is returned if no data are found.
     *
     * @throws DBALException
     */
    public function findById(string $id)
    {
        $columns = Db::getInstance()->fetchAssoc(
            'SELECT * FROM '. Db::ACTORS .' WHERE id = '. $id
        );

        if(!$columns) {
            return false;
        }

        $actor = new Actor();
        self::hydrate($actor, $columns);

        return $actor;
    }

    /**
     * @param Actor $actor
     *
     * @throws DBALException
     */
    public function create(Actor $actor): bool
    {
        $actor->getId() ?? $actor->setId(Db::generateUuid());
        $actor->getRole() ?? $actor->setRole(Db::ROLE_DEFAULT);

        return (bool) Db::getInstance()->insert(
            Db::ACTORS,
            $actor->selfSerialize()
        );
    }

    /**
     * @param Actor $actor
     *
     * @return bool
     *
     * @throws DBALException
     */
    public function update(Actor $actor): bool
    {
        return (bool) Db::getInstance()->update(
            Db::ACTORS,
            $actor->selfSerialize(),
            ['id' => $actor->getId()]
        );
    }
}