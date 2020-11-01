<?php

namespace PsumsApi\Classes;
use PDO;
use PDOException;
use PsumsApi\Config\Config;

/**
 * Class Database
 * @package PsumsApi\Classes
 *
 * Class for interacting with database. Uses PDO
 * https://www.php.net/manual/en/book.pdo.php
 *
 */
class Database
{
    private $pdo = null;
    private $typeToPdoMapping = array(
        'string' => PDO::PARAM_STR,
        'integer' => PDO::PARAM_INT
    );

    public function __construct() {
        if(is_null($this->pdo)) {
            $this->initDatabase();
        }
    }

    /**
     * Initializes connection to mysql database.
     * Generates PDO object
     */
    private function initDatabase() {
        $username = Config::get(Config::DB_USERNAME, "");
        $password = Config::get(Config::DB_PASSWORD, "");
        $host = Config::get(Config::DB_HOST, "");
        $db = Config::get(Config::DB_NAME, "");
        $charset = Config::get(Config::DB_CHARSET, "");

        $dsn = sprintf("mysql:dbname=%s;host=%s;charset=%s;",$db, $host, $charset);
        try {
            $pdo = new PDO($dsn, $username, $password);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->pdo = $pdo;
        } catch (PDOException $e) {
            //TODO batter handling maybe?
            throw $e;
        }
    }

    /**
     *
     * Returns setting from database settings table
     *
     * @param string $settingName
     * @param string|null $default
     * @return mixed|string|null
     */
    public function getSetting(string $settingName, ?string $default="") {
        $setting = $this->select("SELECT value FROM settings WHERE name = ?", array("string"), array($settingName));
        if(!$setting) {
            return $default;
        }

        return $setting[0]["value"];
    }

    public function beginTransaction() {
        if(!$this->pdo->inTransaction()) {
            $this->pdo->beginTransaction();
        }
    }

    public function commit() {
        if($this->pdo->inTransaction()) {
            $this->pdo->commit();
        }
    }

    public function rollBack() {
        if($this->pdo->inTransaction()) {
            $this->pdo->rollBack();
        }
    }

    /**
     *
     * Executes INSERT, UPDATE or DELETE query
     *
     * @param $query
     * @param $types
     * @param $params
     * @return mixed
     */
    public function execute($query, $types, $params) {
        $sth = $this->pdo->prepare($query);
        $this->bindParams($sth, $params, $types);
        return $sth->execute();
    }

    /**
     *
     * Executes SELECT query
     *
     * @param string $query
     * @param array|null $types
     * @param array|null $params
     * @param bool $assoc
     * @return mixed
     */
    public function select(string $query, ?array $types=array(), ?array $params = array(), $assoc = true) {
        $sth = $this->pdo->prepare($query);
        $this->bindParams($sth, $params, $types);
        if($assoc === true) {
            $sth->setFetchMode(PDO::FETCH_ASSOC);
        } else {
            $sth->setFetchMode(PDO::FETCH_FUNC);
        }
        $sth->execute();

        return $sth->fetchAll();
    }

    public function getLastInsertedId(string $table) {
        $lastId =$this->select(sprintf("SELECT id FROM %s ORDER BY id DESC LIMIT 1", $table), array(), array());
        if(!$lastId) {
            return 0;
        }
        return $lastId[0]["id"];
    }

    private function bindParams($sth, array $params, array $types) {
        for($i = 1; $i <= count($params); $i++) {
            if(!empty($types)) {
                $sth->bindParam($i, $params[$i -1], $this->typeToPdoMapping[$types[$i -1]]);
            } else {
                $sth->bindParam($i, $params[$i -1]);
            }
        }
    }

}