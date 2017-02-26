<?php

namespace App\Services\ValidationService\Tests\Tender;

use App\Services\ValidationService\ValidationService;
use Illuminate\Support\Facades\DB;

class ValidationServiceTest extends \TestCase
{
    public function setUp()
    {
        parent::setUp();
        DB::beginTransaction();
    }

    public function tearDown()
    {
        DB::rollBack();
        parent::tearDown();
    }

    public static function tenderSuccessValidateProvider()
    {
        return [
            [ValidationService::TENDER, [
                "tender_id" => "0",
                "type_id" => "4",
                "procurement_type_id" => "1",
                "title" => "title",
                "description" => "desc",
                "currency_id" => "1",
                "contact_person" => "1",
                "contact_name" => "DexMax corp",
                "contact_phone" => "+380621234354",
                "contact_email" => "asfsadf@fdfssd.fds",
                "contact_url" => "http://zakupki.ua",
                "enquiry_end_date" => date('d.m.Y H:i', time() + 86400),
                "tender_end_date" => date('d.m.Y H:i', time() + 172800),
                "lots" => [
                    0 => [
                        "id" => "",
                        "title" => "lot title",
                        "description" => "lot desc",
                        "amount" => "1234500",
                        "minimal_step" => "12345",
                        "guarantee_type" => "ns",
                        "items" => [
                            0 => [
                                "id" => "",
                                "description" => "item desc",
                                "quantity" => "3",
                                "unit_id" => "1",
                                "cpv" => "34000000-7 Транспортне обладнання та допоміжне приладдя до нього",
                                "codes" => [
                                    1 => [
                                        "id" => "14389"
                                    ]
                                ],
                                "additionalClassifier" => "2",
                                "delivery_date_start" => date('d.m.Y', time() + 259200),
                                "delivery_date_end" => date('d.m.Y', time() + 345600),
                                "same_delivery_address" => "1",
                                "region_id" => "26",
                                "postal_code" => "58042",
                                "locality" => "Kiev",
                                "delivery_address" => "Победы, 13"
                            ]
                        ]
                    ]
                ]
            ], 'belowThreshold'],
            [ValidationService::TENDER, [
                "tender_id" => "0",
                "type_id" => "4",
                "procurement_type_id" => "1",
                "title" => "title",
                "description" => "desc",
                "currency_id" => "1",
                "contact_person" => "1",
                "contact_name" => "DexMax corp",
                "contact_phone" => "+380621234354",
                "contact_email" => "asfsadf@fdfssd.fds",
                "contact_url" => "http://zakupki.ua",
                "enquiry_end_date" => date('d.m.Y H:i', time() + 86400),
                "tender_end_date" => date('d.m.Y H:i', time() + 172800),
                "lots" => [
                    0 => [
                        "id" => "",
                        "title" => "lot title",
                        "description" => "lot desc",
                        "amount" => "1234500",
                        "minimal_step" => "12345",
                        "guarantee_type" => "ns",
                        "items" => [
                            0 => [
                                "id" => "",
                                "description" => "item desc",
                                "quantity" => "3",
                                "unit_id" => "1",
                                "cpv" => "99999999-9 Не визначено",
                                "dkpp" => "17.12.1 Папір газетний, папір ручного виготовляння та інший некрейдований папір, або картон для графічних цілей",
                                "codes" => [
                                    1 => [
                                        "id" => "20527"
                                    ],
                                    2 => [
                                        "id" => "2367"
                                    ]
                                ],
                                "additionalClassifier" => "2",
                                "delivery_date_start" => date('d.m.Y', time() + 259200),
                                "delivery_date_end" => date('d.m.Y', time() + 345600),
                                "same_delivery_address" => "1",
                                "region_id" => "26",
                                "postal_code" => "58042",
                                "locality" => "Kiev",
                                "delivery_address" => "Победы, 13"
                            ],
                            1 => [
                                "id" => "",
                                "description" => "item desc 2",
                                "quantity" => "3",
                                "unit_id" => "1",
                                "cpv" => "99999999-9 Не визначено",
                                "dkpp" => "17.12.12-00.00 Папір і картон ручного виготовляння",
                                "codes" => [
                                    1 => [
                                        "id" => "20527"
                                    ],
                                    2 => [
                                        "id" => "2371"
                                    ]
                                ],
                                "additionalClassifier" => "2",
                                "delivery_date_start" => date('d.m.Y', time() + 259200),
                                "delivery_date_end" => date('d.m.Y', time() + 345600),
                                "same_delivery_address" => "1",
                                "region_id" => "26",
                                "postal_code" => "58042",
                                "locality" => "Kiev",
                                "delivery_address" => "Победы, 13"
                            ]
                        ]
                    ]
                ]
            ], 'belowThreshold'],
            [ValidationService::TENDER, [
                "tender_id" => "0",
                "type_id" => "4",
                "title" => "title",
                "description" => "desc",
                "amount" => "1234500",
                "currency_id" => "1",
                "contact_person" => "1",
                "contact_name" => "DexMax corp",
                "contact_phone" => "+380621234354",
                "contact_email" => "asfsadf@fdfssd.fds",
                "contact_url" => "http://zakupki.ua",
                "lots" => [
                    0 => [
                        "id" => "",
                        "items" => [
                            0 => [
                                "id" => "",
                                "description" => "item desc",
                                "quantity" => "3",
                                "unit_id" => "1",
                                "cpv" => "34000000-7 Транспортне обладнання та допоміжне приладдя до нього",
                                "codes" => [
                                    1 => [
                                        "id" => "14389"
                                    ]
                                ],
                                "additionalClassifier" => "2",
                                "delivery_date_start" => date('d.m.Y', time() + 86400),
                                "delivery_date_end" => date('d.m.Y', time() + 172800),
                                "same_delivery_address" => "1",
                                "region_id" => "26",
                                "postal_code" => "58042",
                                "locality" => "Kiev",
                                "delivery_address" => "Победы, 13"
                            ]
                        ]
                    ]
                ]
            ], 'reporting']
        ];
    }

    public static function tenderFailValidateProvider()
    {
        return [
            [ValidationService::TENDER, [], 'belowThreshold'],
            [ValidationService::TENDER, [], 'reporting'],
            [ValidationService::TENDER, [
                "tender_id" => "0",
                "type_id" => "4",
                "procurement_type_id" => "1",
                "title" => "title",
                "description" => "desc",
                "currency_id" => "1",
                "contact_person" => "1",
                "contact_name" => "DexMax corp",
                "contact_phone" => "+380621234354",
                "contact_email" => "asfsadf@fdfssd.fds",
                "contact_url" => "http://zakupki.ua",
                "enquiry_end_date" => date('d.m.Y H:i', time() + 86400),
                "tender_end_date" => date('d.m.Y H:i', time() + 172800),
                "lots" => [
                    0 => [
                        "id" => "",
                        "title" => "lot title",
                        "description" => "lot desc",
                        "amount" => "1234500",
                        "minimal_step" => "12345",
                        "guarantee_type" => "ns",
                        "items" => [
                            0 => [
                                "id" => "",
                                "description" => "item desc",
                                "quantity" => "3",
                                "unit_id" => "1",
                                "cpv" => "99999999-9 Не визначено",
                                "dkpp" => "22.29.91-30.00 Виготовляння пластмасових деталей до пристроїв підкатегорій 26.30.11, 26.30.12, 26.30.13, 26.40.33 (до відеокамер), 26.70.13, до виробів підкатегорій 26.20.17, 26.40.20, 26.40.34",
                                "codes" => [
                                    1 => [
                                        "id" => "20527"
                                    ],
                                    2 => [
                                        "id" => "3805"
                                    ]
                                ],
                                "additionalClassifier" => "2",
                                "delivery_date_start" => date('d.m.Y', time() + 259200),
                                "delivery_date_end" => date('d.m.Y', time() + 345600),
                                "same_delivery_address" => "1",
                                "region_id" => "26",
                                "postal_code" => "58042",
                                "locality" => "Kiev",
                                "delivery_address" => "Победы, 13"
                            ],
                            1 => [
                                "id" => "",
                                "description" => "item desc 2",
                                "quantity" => "3",
                                "unit_id" => "1",
                                "cpv" => "99999999-9 Не визначено",
                                "dkpp" => "22.29.91-30.00 Виготовляння пластмасових деталей до пристроїв підкатегорій 26.30.11, 26.30.12, 26.30.13, 26.40.33 (до відеокамер), 26.70.13, до виробів підкатегорій 26.20.17, 26.40.20, 26.40.34",
                                "codes" => [
                                    1 => [
                                        "id" => "20527"
                                    ],
                                    2 => [
                                        "id" => "5243"
                                    ]
                                ],
                                "additionalClassifier" => "2",
                                "delivery_date_start" => date('d.m.Y', time() + 259200),
                                "delivery_date_end" => date('d.m.Y', time() + 345600),
                                "same_delivery_address" => "1",
                                "region_id" => "26",
                                "postal_code" => "58042",
                                "locality" => "Kiev",
                                "delivery_address" => "Победы, 13"
                            ]
                        ]
                    ]
                ]
            ], 'belowThreshold']
        ];
    }

    /**
     * @param $objectName
     * @param $data
     * @param $objectType
     *
     * @dataProvider tenderSuccessValidateProvider
     */
    public function testSuccessValidate($objectName, $data, $objectType) {
        $validator = ValidationService::validate($objectName, $data, $objectType);
        $this->assertTrue($validator->fails() === false);
    }

    /**
     * @param $objectName
     * @param $data
     * @param $objectType
     *
     * @dataProvider tenderFailValidateProvider
     */
    public function testFailValidate($objectName, $data, $objectType) {
        $validator = ValidationService::validate($objectName, $data, $objectType);
        $this->assertTrue($validator->fails() === true);
    }
}