<?php

namespace PsumsApi\Classes;
use Psums\Classes\Factory;

/**
 * Class Stream
 * @package PsumsApi\Classes
 */
class Stream
{
    public function getStreamModel() {
        return Factory::getModel(Factory::MODEL_STREAM);
    }
}