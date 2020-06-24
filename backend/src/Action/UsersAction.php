<?php
declare(strict_types=1);

namespace App\Action;

use App\Domain\Mapper\UserMapper;
use App\Exception\BadEventFormatException;
use App\Exception\EntityNotFoundException;
use App\Exception\MissingIdEventFormatException;

class UsersAction extends CrudAction
{
    public function create()
    {
        return $this->respondWithData(false);
    }

    public function read()
    {
        $body = $this->event['body'];
        $mapper = new UserMapper();

        if (!array_key_exists('id', $body)) {
            $users = $mapper->findAll();
            return $this->respondWithData($users);
        }

        $id = $body['id'];
        $user = $mapper->findById($id);
        
        if (!$user) {
            throw new EntityNotFoundException;
        }

        return $this->respondWithData($user);
    }

    public function update()
    {
        if(
            !array_key_exists('id', $this->event['body'])
            || !array_key_exists('role', $this->event['body'])
        ) {
            throw new BadEventFormatException;
        }

        $status = false;

        $id = $this->event['body']['id'];
        $role = $this->event['body']['role'];

        $mapper = new UserMapper();
        $user = $mapper->findById($id);

        if(!empty($user)) {
            // $mapper->hydrate($user, $this->event['body'], true);
            $user->setRole($role);
            $status = $mapper->update($user);
        }

        return $this->respondWithData($status);
    }

    public function delete()
    {
        if (!array_key_exists('id', $this->event['body'])) {
            throw new MissingIdEventFormatException;
        }

        $id = $this->event['body']['id'];
        $status = (new UserMapper())->deleteById($id);

        return $this->respondWithData($status);
    }
}
