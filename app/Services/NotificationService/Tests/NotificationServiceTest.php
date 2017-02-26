<?php

namespace App\Services\NotificationService\Tests;

use App\Model\Notification;
use App\Services\NotificationService\Model\NotificationTemplate;
use App\Services\NotificationService\NotificationService;
use App\Services\NotificationService\Tags;
use Illuminate\Support\Facades\DB;

class NotificationServiceTest extends \TestCase
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

    public static function notificationAliasesProvider()
    {
        return array(
            array('test.alias.success', 'test.alias.success', 1, 'ua', null),
            array('test.alias.success', 'test.alias.unsuccess', 1, 'ua', 'Parameter - template is empty. '),
            array('test.alias.success', null, 1, 'ua', 'Parameter - template is empty. Parameter - alias is empty. '),
            array('test.alias.success', 'test.alias.success', null, 'ua', 'Parameter - user_id is empty. '),
        );
    }

    public static function notificationAliasesAndTagsProvider()
    {
        return array(
            array(
                'test.alias.success',
                'success',
                'Test text with exist tag - [[tender_link]]',
                'Test text with exist tag - success',
                null
            ),
            array(
                'test.alias.unsuccess',
                'unsuccess tag',
                'Test text with not exist tag - success [[unsuccess_tag]]',
                'Test text with not exist tag - success ',
                null
            ),
            array(
                'test.alias.unsuccess',
                'unsuccess tag',
                'Test text without tags - success',
                'Test text without tags - success',
                null
            ),
            array(
                'test.alias.unsuccess',
                null,
                'Test text with tagname null - success [[some]]',
                'Test text with tagname null - success ',
                null
            ),
            array(
                'test.alias.unsuccess',
                '.',
                'Test text with tagname symbol - success [[some]]',
                'Test text with tagname symbol - success ',
                null
            ),
            array(
                'test.alias.unsuccess',
                1,
                'Test text with tagname integer - success [[some]]',
                'Test text with tagname integer - success ',
                null
            )
        );
    }

    public static function notificationAliasesAndNotExistTagsProvider()
    {
        return array(
            array(
                'test.alias.success',
                'new_tag',
                'success',
                'Test text with new tag - [[new_tag]]',
                'Test text with new tag - success',
                null
            ),
            array(
                'test.alias.unsuccess',
                'new_tag',
                'new tag',
                'Test text with new tag which not exist in text - success [[old_tag]]',
                'Test text with new tag which not exist in text - success ',
                null
            ),
            array(
                'test.alias.unsuccess',
                null,
                null,
                'Test text with new tag (key = null, value = null) - success [[some_tag]]',
                'Test text with new tag (key = null, value = null) - success ',
                null
            ),
            array(
                'test.alias.unsuccess',
                null,
                'new tag',
                'Test text with new tag (key = null) - success [[some_tag]]',
                'Test text with new tag (key = null) - success ',
                null
            ),
            array(
                'test.alias.unsuccess',
                'new_tag',
                null,
                'Test text with new tag (value = null) - success [[new_tag]]',
                'Test text with new tag (value = null) - success ',
                null
            ),
        );
    }

    /**
     * @param $alias_for_table
     * @param $alias_for_request
     * @param $result
     * @param $user_id
     * @param $lang
     *
     * @dataProvider notificationAliasesProvider
     */
    public function testSuccessNotificationWithoutTags($alias_for_table, $alias_for_request, $user_id, $lang, $result)
    {
        $this->createNotificationTemplate($alias_for_table);
        $service = new NotificationService();
        $tags = new Tags();
        $test_result = $service->create($tags, $alias_for_request, $user_id, $lang);

        $this->assertTrue($test_result === $result);
    }

    /**
     * @param $alias
     * @param $tag
     * @param $text
     * @param $result_text
     * @param $result
     *
     * @dataProvider notificationAliasesAndTagsProvider
     */
    public function testSuccessNotificationWithExistTags($alias, $tag, $text, $result_text, $result)
    {
        $this->createNotificationTemplate($alias, $text);
        $service = new NotificationService();
        $tags = new Tags();
        $tags->set_tender_link($tag);
        $test_result = $service->create($tags, $alias, 1, 'ua');
        $last_notification = Notification::orderBy('id', 'desc')->first();
        var_dump($last_notification->text);

        $this->assertTrue($test_result === $result);
        $this->assertTrue($last_notification->text === $result_text);
    }

    /**
     * @param $alias
     *
     * @dataProvider notificationAliasesAndNotExistTagsProvider
     */
    public function testSuccessNotificationWithNotExistTags($alias, $tag_key, $tag_value, $text, $result_text, $result)
    {
        $this->createNotificationTemplate($alias, $text);
        $service = new NotificationService();
        $tags = new Tags();
        $tags->set($tag_key, $tag_value);
        $test_result = $service->create($tags, $alias, 1, 'ua');
        $last_notification = Notification::orderBy('id', 'desc')->first();
        var_dump($last_notification->text);

        $this->assertTrue($test_result === $result);
        $this->assertTrue($last_notification->text == $result_text);
    }


    /**
     * @param string $alias
     */
    private function createNotificationTemplate($alias, $text = 'Default text')
    {
        $template = new NotificationTemplate();
        $template->alias = $alias;
        $template->title = 'Test notification';
        $template->description = $text;
        $template->lang = 'ua';
        $template->active = 1;
        $template->save();
    }
}