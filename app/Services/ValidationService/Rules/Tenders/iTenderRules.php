<?php

namespace App\Services\ValidationService\Rules\Tenders;

/**
 * interface for tender`s type rules
 * Interface iTenderRules
 * @package App\Services\ValidationService\Rules\Tenders
 */
interface iTenderRules {

    /**
     * @return iTenderRules
     */
    static function getInstance();

    /**
     * @return array
     */
    function getTenderRules();

    /**
     * @return array
     */
    function getTenderMessages();

    /**
     * @return array
     */
    function getLotRules();

    /**
     * @param null $lotIndex
     * @return array
     */
    function getLotMessages($lotIndex);

    /**
     * @param array $itemCodes
     * @return array
     */
    function getItemRules($itemCodes);

    /**
     * @param null $lotIndex
     * @param null $itemIndex
     * @param array $itemCodes
     * @return array
     */
    function getItemMessages($lotIndex, $itemIndex, $itemCodes);
}