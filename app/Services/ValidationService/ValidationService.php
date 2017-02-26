<?php

namespace App\Services\ValidationService;


use App\Services\ValidationService\Rules\Tenders\BaseValidation;
use App\Services\ValidationService\Rules\Tenders\BelowThresholdValidation;
use App\Services\ValidationService\Rules\Tenders\iTenderRules;
use App\Services\ValidationService\Rules\Tenders\ReportingValidation;
use Illuminate\Support\Facades\Validator;

/**
 * Class ValidationService
 * @package App\Services\ValidationService
 */
class ValidationService
{
    const TENDER = 'Tender';
    const LOT = 'Lot';
    const ITEM = 'Item';

    /**
     * validate data for $objectName
     * @param $objectName
     * @param $data
     * @param null $objectType
     * @return Validator|string
     * @throws \Exception
     */
    public static function validate($objectName, $data, $objectType = null)
    {
        $objectName = "App\\Services\\ValidationService\\Model\\" . $objectName;
        if (!class_exists($objectName))
            throw new \Exception('Bad object name');

        if ($objectType) {
            $tenderTypeInstance = self::getTenderRulesInstance($objectType);
            $object = new $objectName($data, $tenderTypeInstance);
        } else {
            $object = new $objectName($data);
        }

        return Validator::make($object->getData(), $object->getRules(), $object->getMessages());
    }

    /**
     * get rules instance of tender type
     * @param $objectType
     * @return iTenderRules
     */
    protected static function getTenderRulesInstance($objectType)
    {
        switch ($objectType) {
            case 'belowThreshold':
                return BelowThresholdValidation::getInstance();
//            case 'aboveThresholdUA':
//                return BelowThresholdValidation::getInstance();
//            case 'aboveThresholdEU':
//                return BelowThresholdValidation::getInstance();
            case 'reporting':
                return ReportingValidation::getInstance();
//            case 'negotiation':
//                return BelowThresholdValidation::getInstance();
//            case 'negotiation.quick':
//                return BelowThresholdValidation::getInstance();
//            case 'aboveThresholdUA.defense':
//                return BelowThresholdValidation::getInstance();
//            case 'competitiveDialogueUA':
//                return BelowThresholdValidation::getInstance();
//            case 'competitiveDialogueEU':
//                return BelowThresholdValidation::getInstance();
//            case 'competitiveDialogueUA.stage2':
//                return BelowThresholdValidation::getInstance();
//            case 'competitiveDialogueEU.stage2':
//                return BelowThresholdValidation::getInstance();
            default:
                return BaseValidation::getInstance();
        }
    }
}