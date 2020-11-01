<?php

namespace PsumsApi\Models;

use Exception;
use Psums\Classes\Factory;
use PsumsApi\Classes\Validator;

/**
 * Class Model
 * @package PsumsApi\Models
 *
 * Model class for interacting with database
 * Implements base logic, required for child classes to extend it
 *
 */
abstract class Model
{
    protected $validator;

    protected $idField = 'id'; //override in child classes
    protected $table; //override in child class
    protected $dataSet = array("id"); //override in child class;

    public function __construct(Validator $validator) {
        $this->validator = $validator;
    }

    /**
     *
     * $insertData = array(field1 => value1, field2 => value2...)
     *
     * @param array $insertData
     * @param bool|null $lastId
     * @return int|mixed
     * @throws Exception
     */
    public function insert(array $insertData, ?bool $lastId=false) {
        $keys = array_keys($insertData);
        $this->validateDataSet($keys, "insert");
        $keys = array_map(function($key) {
            return $this->validator->filterAlphaDash($key);
        }, $keys);
        $values = array_values($insertData);
        $holders = array_fill(0, count($values), "?");

        $keysStr = implode(",", $keys);
        $holdersStr = implode(",", $holders);
        $query = sprintf("INSERT INTO %s (%s) VALUES (%s)", $this->table, $keysStr, $holdersStr);

        Factory::getObject(Factory::TYPE_DATABASE, true)->execute($query, array(), $values);
        if($lastId === true) {
            return Factory::getObject(Factory::TYPE_DATABASE, true)->getLastInsertedId($this->table);
        }
    }

    /**
     *
     * $fields = array(field1, field2, field3....)
     * $where = array()|array(field1 => value1, field2 => value2....)
     *
     * @param array|null $fields
     * @param array|null $where
     * @return mixed
     * @throws Exception
     */
    public function select(?array $fields=array(), ?array $where=array()) {
        $this->validateDataSet($fields, "fields");
        $this->validateDataSet(array_keys($where), "where");

        if(empty($fields)) {
            $fields = $this->dataSet;
        }

        $query = "SELECT ";
        $fields = array_map(function($value) {
            return $this->validator->filterAlphaDash($value);
        }, $fields);
        $fieldsStr = implode(",", $fields);
        $query .= $fieldsStr . " FROM " . $this->table;

        $values = array();
        if(!empty($where)) {
            $query .= " WHERE";
            $keys = array_keys($where);
            $values = array_values($where);
            $keys = array_map(function($value) {
                return $this->validator->filterAlphaDash($value);
            }, $keys);
            array_walk($keys, function ($key) use(&$query) {
                $query .= sprintf(" %s=? AND", $key);
            });
            $query = substr($query, 0, strlen($query) - strlen(" AND"));
        }

        return Factory::getObject(Factory::TYPE_DATABASE, true)->select($query, array(), $values);
    }

    /**
     *
     * $fields = array(field1 => value1, field2 => value2.....)
     * $where = array()|array(field1 => value1, field2 => value2....)
     *
     * @param array $fields
     * @param array|null $where
     * @return mixed
     * @throws Exception
     */
    public function update(array $fields, ?array $where) {
        $this->validateDataSet(array_keys($fields), "fields");
        $this->validateDataSet(array_keys($where), "where");

        $query = sprintf("UPDATE %s SET ", $this->table);
        $values = array_values($fields);
        $fieldsKeys = array_keys($fields);
        array_walk($fieldsKeys, function($key) use (&$query) {
            $key = $this->validator->filterAlphaNumUnderscore($key);
            $query .= sprintf("%s=?,", $key);
        });
        $query = substr($query, 0, strlen($query) - 1);

        if(!empty($where)) {
            $query .= " WHERE";
            $whereKeys = array_keys($where);
            $values = array_merge($values, array_values($where));
            array_walk($whereKeys, function($key) use(&$query) {
                $key = $this->validator->filterAlphaNumUnderscore($key);
                $query .= sprintf(" %s=? AND", $key);
            });
            $query = substr($query, 0, strlen($query) - strlen(" AND"));
        }

        return Factory::getObject(Factory::TYPE_DATABASE, true)->execute($query, array(), $values);
    }

    /**
     *
     * Deletes by id or by where arguments
     * $where = array()|array(field1 => value1, field2 => value2....)
     *
     * @param int|null $id
     * @param array|null $where
     * @throws Exception
     */
    public function delete(?int $id=0, ?array $where=array()) {
        $this->validateDataSet(array_keys($where), "where");
        if(empty($where)) {
            $keys = array($this->idField);
            $values = array($id);
        } else {
            $keys = array_keys($where);
            $values = array_values($where);
        }

        $query = sprintf("DELETE FROM %s WHERE ", $this->table);
        array_walk($keys, function($key) use(&$query) {
           $key = $this->validator->filterAlphaNumUnderscore($key);
           $query .= sprintf("%s=? AND", $key);
        });
        $query = substr($query, 0, strlen($query) - strlen(" AND"));

        Factory::getObject(Factory::TYPE_DATABASE, true)->execute($query, array(), $values);
    }

    /**
     *
     * check if all supplied fields exists in table data set
     *
     * @param array $datasetToValidate
     * @param $name
     * @throws Exception
     */
    private function validateDataSet(array $datasetToValidate, $name) {
        $invalidKeys = array_diff($datasetToValidate, $this->dataSet);
        if(!empty($invalidKeys)) {
            throw new Exception("Invalid keys in set " . $name);
        }
    }
}