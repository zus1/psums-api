<?php

namespace PsumsApi\Classes;
use Memcached;

/**
 * Class Cache
 * @package PsumsApi\Classes
 *
 * Class for interacting with Memcached
 * https://www.php.net/manual/en/intro.memcached.php
 *
 */
class Cache
{

    private static $_mc;
    private static $_initialized = false;

    /**
     * Initializes connection to Memcached service
     */
    private static function init() {
        if(self::$_initialized === false) {
            $mc = new Memcached();
            $mc->addServer("memcached", 11211);
            self::$_mc = $mc;
            self::$_initialized = true;
        }
    }

    /**
     *
     * Checks if cached key expired
     *
     * @param string $key
     * @param int|null $ttl
     * @return bool
     */
    public static function shouldIRun(string $key, ?int $ttl=60) {
        self::init();

        $entry = self::$_mc->get($key);
        if(!$entry) {
            self::$_mc->set($key, $key, $ttl);
            return true;
        }

        return false;
    }
}