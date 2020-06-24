<?php
declare(strict_types=1);

namespace App\Domain;

abstract class Entity implements EntityInterface
{
    public function jsonRead(string $json, string $key)
    {
        $decoded = json_decode($json ?? '{}', true);

        if (!array_key_exists($key, $decoded)) {
            return null;
        }

        return $decoded[$key];
    }

    public function jsonInsert(string &$json, string $key, string $value): void
    {
        $decoded = json_decode($json ?? '{}', true);
        $decoded[$key] = $value;

        $json = json_encode($decoded, JSON_FORCE_OBJECT);
    }


    /**
     * @param string $value
     * @return bool Retruns false if passed email is already exists
     */
    public function jsonArrayInsert(string &$json, string $key, string $value): bool
    {
        $decoded = json_decode($json ?? '{}', true);

        if (!array_key_exists($key, $decoded)) {
            $decoded[$key] = [];
        } elseif(in_array($value, $decoded[$key])) {
            return false;
        }
        $decoded[$key][] = $value;

        $json = json_encode($decoded, JSON_FORCE_OBJECT);
        return true;
    }
}