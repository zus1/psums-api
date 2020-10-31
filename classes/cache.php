<?php

class Cache
{

    private static $_mc;
    private static $_initialized = false;

    private static function init() {
        if(self::$_initialized === false) {
            $mc = new Memcached();
            $mc->addServer("memcached", 11211);
            self::$_mc = $mc;
            self::$_initialized = true;
        }
    }

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