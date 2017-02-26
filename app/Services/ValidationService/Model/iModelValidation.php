<?php

namespace App\Services\ValidationService\Model;

/**
 * interface for validate model
 * Interface iModelValidation
 * @package App\Services\ValidationService\Model
 */
interface iModelValidation {

    /**
     * @return array
     */
    function getRules();

    /**
     * @return array
     */
    function getData();

    /**
     * @return array
     */
    function getMessages();
}