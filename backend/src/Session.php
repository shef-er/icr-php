<?php
declare(strict_types=1);

namespace App;

class Session
{
    /**
     * @return void
     */
    public static function start(): void
    {
        // set session lifetime to 100 days
        session_set_cookie_params(60 * 60 * 24 * 100);

        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
    }

    /**
     * @return bool
     */
    public static function close(): bool
    {
        return session_write_close();
    }

    /**
     * @param string $key
     * 
     * @return bool
     */

    public static function isset(string $key): bool
    {
        return isset($_SESSION) && array_key_exists($key, $_SESSION);
    }

    /**
     * @param string $key
     * 
     * @return mixed|null
     */
    public static function get(string $key)
    {
        return self::isset($key) ? $_SESSION[$key] : null;
    }

    /**
     * @param string $key
     * @param mixed $value
     * 
     * @return bool
     */
    public static function set(string $key, $value): bool
    {
        if(!isset($_SESSION)) {
            return false;
        }

        $_SESSION[$key] = $value;
        return true;
    }

    /**
     * @param string $keys,...
     * 
     * @return void
     */
    public static function unset(string ...$keys): void
    {
        foreach ($keys as $key) {
            if(self::isset($key)) {
                unset($_SESSION[$key]);
            }
        }
    }
}