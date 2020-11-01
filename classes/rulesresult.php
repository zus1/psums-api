<?php

namespace PsumsApi\Classes;
use Psums\Classes\Factory;

/**
 * Class RulesResult
 * @package PsumsApi\Classes
 */
class RulesResult
{
    public function getModel() {
        return Factory::getModel(Factory::MODEL_RULES_RESULT);
    }
}