<?php
declare(strict_types=1);

namespace App\Domain\Mapper;

use App\Db;
use App\Domain\Entity\User;

class UserMapper extends DataMapper
{
    /**
     * @return User[]|false False is returned if no data are found.
     *
     * @throws DBALException
     */
    public function findAll()
    {
        $rows = Db::getInstance()->fetchAll(
            'SELECT * FROM ' . Db::USERS . ' AS u' .
            ' JOIN ' . Db::ACTORS . ' AS a 
            ON a.id = u.id'
        );

        if(!$rows) {
            // return [];
            return false;
        }

        $users = [];
        foreach ($rows as $row) {
            $user = new User();
            self::hydrate($user, $row);
            $users[] = $user;
        }

        return $users;
    }

    /**
     * @param string $id
     *
     * @return User|false False is returned if no data are found.
     *
     * @throws DBALException
     */
    public function findById(string $id)
    {
        $columns = Db::getInstance()->fetchAssoc(
            'SELECT * FROM ' . Db::USERS . ' AS u' .
            ' JOIN ' . Db::ACTORS . ' AS a 
            ON a.id = u.id
            WHERE u.id = \'' . $id .'\''
        );

        if(!$columns) {
            return false;
        }

        $user = new User();
        self::hydrate($user, $columns);

        return $user;
    }

    /**
     * @param string $uid
     *
     * @return User|false False is returned if no data are found.
     *
     * @throws DBALException
     */
    public function findByGoogleUid(string $uid)
    {
        $columns = Db::getInstance()->fetchAssoc(
            'SELECT *
            FROM ' . Db::USERS . ' AS u
            JOIN ' . Db::ACTORS . ' AS a ON a.id = u.id
            WHERE u.credentials::json->>\'google_uid\' = \'' . $uid . '\'
            ORDER BY u.registration_date DESC'
        );

        if(!$columns) {
            return false;
        }

        $user = new User();
        self::hydrate($user, $columns);

        return $user;
    }

    /**
     * @param User $user
     *
     * @return bool
     *
     * @throws DBALException
     */
    public function create(User $user): bool
    {
        if(null === $user->getId()) {
            $user->setId(Db::generateUuid());
        }

        if(null === $user->getRegistrationDate()) {
            $user->setRegistrationDate(date('Y-m-d'));
        }

        if(null === $user->getRole()) {
            $user->setRole(Db::ROLE_DEFAULT);
        }

        return Db::getInstance()->transactional(function($db) use ($user) {
            if(
                (bool) $db->insert(
                    Db::ACTORS,
                    $user->parentSerialize(true)
                )
                &&
                (bool) $db->insert(
                    Db::USERS,
                    $user->selfSerialize()
                )
            ) {
                return true;
            } else {
                return false;
            }
        });
    }

    /**
     * @param User $user
     *
     * @return bool
     *
     * @throws DBALException
     */
    public function update(User $user): bool
    {
        return Db::getInstance()->transactional(function($db) use ($user) {
            $id = $user->getId();
            if(
                (bool) $db->update(
                    Db::ACTORS,
                    $user->parentSerialize(),
                    ['id' => $id]
                )
                && 
                (bool) $db->update(
                    Db::USERS,
                    $user->selfSerialize(),
                    ['id' => $id]
                )
            ) {
                return true;
            } else {
                return false;
            }
        });
    }

    /**
     * @param User $user
     *
     * @return bool
     *
     * @throws DBALException
     */
    public function deleteById(string $id): bool
    {
        return Db::getInstance()->transactional(function($db) use ($id) {
            if(
                (bool) $db->delete(
                    Db::ACTORS,
                    ['id' => $id]
                )
                &&
                (bool) $db->delete(
                    Db::USERS,
                    ['id' => $id]
                )
            ) {
                return true;
            } else {
                return false;
            }
        });
    }
}