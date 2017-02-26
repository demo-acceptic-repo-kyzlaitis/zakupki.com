<?php

namespace App\Services\NotificationService\Tests;

use App\Model\Notification;
use App\Model\Organization;
use App\Model\Question;
use App\Model\Tender;
use App\Providers\EventServiceProvider;
use App\Services\NotificationService\Model\NotificationTemplate;
use App\Services\NotificationService\NotificationService;
use App\Services\NotificationService\Tags;
use Illuminate\Foundation\Application;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\Request;
use Illuminate\Support\Facades\URL;

class EventServiceProviderTest extends \TestCase
{
    private $_tender;
    private $_question;
    private $_organization;

    public function setUp()
    {
        parent::setUp();
        $this->_organization = $this->createDefaultOrganization();
        $this->_tender = $this->createDefaultTender($this->_organization);
        DB::beginTransaction();
    }

    public function tearDown()
    {
        DB::rollBack();
        parent::tearDown();
    }

    public function testSendNotification()
    {
        $app = new Application();
        $tags = new Tags();
        $provider_child = new EventServiceProviderChild($app);
        $this->_tender = Tender::orderBy('id', 'asc')->first();;
        $provider_child->sendNotification($tags, $this->_tender, NotificationTemplate::TENDER_PUBLISHED);
        $last_notification = Notification::orderBy('id', 'desc')->first();

        $this->assertTrue($last_notification->alias == NotificationTemplate::TENDER_PUBLISHED);
    }

    /**
     * @param Organization $organization
     * @return Tender
     */
    private function createDefaultTender(Organization $organization)
    {
        $attributes = [
            "mode" => 0,
            "multilot" => 1,
            "source" => 1,
            "organization_id" => $organization->id,
            "tenderID" => "UA-2016-07-27-000259-1",
            "type_id" => 1,
            "procurement_type_id" => 1,
            "cause_description" => "",
            "cause" => "",
            "title" => "[ТЕСТУВАННЯ] Тест допорог ",
            "title_en" => null,
            "description" => "йухйух",
            "description_en" => "",
            "amount" => 5000000,
            "currency_id" => 1,
            "tax_included" => 0,
            "minimal_step" => 50000,
            "number_of_bids" => 0,
            "auction_url" => "",
            "status" => "complete",
            "blocked" => 0,
            "signed" => 0,
            "cbd_id" => "0ed10ee24a654e829e31080bfb00a0fd",
            "access_token" => "c1263f99b79d4522b1d95aa58e5aee2a",
            "contact_name" => "Макс Декс",
            "contact_name_en" => null,
            "contact_phone" => "+380991112244",
            "contact_email" => "promo3+1@gmail.com",
            "contact_available_lang" => "",
            "contact_url" => "http://promo3.ua",
        ];
        return Tender::create($attributes);
    }

    /**
     * @return Tender
     */
    private function createDefaultQuestion(Tender $tender, Organization $organization)
    {
        $attributes = [
            "cbd_id" => "6d56603563b64f318261efd54d315f12",
            "organization_id" => 15,
            "organization_to_id" => $organization->id,
            "questionable_id" => $tender->id,
            "tender_id" => $tender->id,
            "questionable_type" => "App\\Model\\Tender",
            "title" => "Тест вопрос ",
            "description" => "test description",
            "answer" => "some answer",
        ];
        return Question::create($attributes);

    }

    /**
     * @return Organization
     */
    private function createDefaultOrganization()
    {
        $attributes = [
            "source" => 1,
            "kind_id" => 1,
            "user_id" => 13,
            "name" => "Управління освіти Шевченківської районної в місті Києві державної адміністрації",
            "legal_name_en" => "",
            "legal_name" => "",
            "name_en" => null,
            "type" => "",
            "identifier" => null,
            "mode" => 0,
            "confirmed" => 1,
            "country_id" => 228,
            "region_id" => 0,
            "region_name" => "місто Київ",
            "postal_code" => "04119",
            "locality" => "Київ",
            "street_address" => "вулиця Молдавська, 6А",
            "contact_name" => "Коваленко Леся Аркадіївна",
            "contact_name_en" => null,
            "contact_email" => "lkovalenko+5@meta.ua",
            "contact_available_lang" => "",
            "contact_phone" => "489-02-47",
            "contact_fax" => "",
            "contact_url" => "",
        ];
        return Organization::create($attributes);

    }

}

class EventServiceProviderChild extends EventServiceProvider
{
    /**
     * @param Model $model
     * @param Tags $tags
     * @param string $alias
     * @param string|null $notificationable_id
     */
    public function sendNotification(Tags $tags, Model $model, $alias, $notificationable_id = null)
    {
        parent::_sendNotification($tags, $model, $alias, $notificationable_id);
    }
}