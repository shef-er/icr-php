<?php
declare(strict_types=1);

namespace App\Action;

use App\Action;
use Psr\Http\Message\ResponseInterface as Response;
use Slim\Exception\HttpBadRequestException;

abstract class CrudAction extends Action {
    /**
     * @var array
     */
    protected $event;

    /**
     * {@inheritdoc}
     */
    protected function action(): Response
    {
        if(
            !$this->validateRequestFormat()
        ) {
            throw new HttpBadRequestException($this->request);
        }

        $this->event = $this->request->getParsedBody();
        switch ($this->event['type']) {
            case 'create':
                return $this->create();
            case 'read':
                return $this->read();
            case 'update':
                return $this->update();
            case 'delete':
                return $this->delete();
        }
    }

    protected function validateRequestFormat()
    {
        $parsedBody = $this->request->getParsedBody();
        
        $format = [
            'type',
            'body',
            'user',
        ];

        $diff = array_diff($format, array_keys($parsedBody));

        return empty($diff);
    }

    abstract public function create();

    abstract public function read();

    abstract public function update();

    abstract public function delete();
}