<?php

namespace App\Services\NotificationService;

use App\Model\Notification;
use App\Services\NotificationService\Model\NotificationTemplate;

class NotificationService
{
    const DEFAULT_LANGUAGE = 'ua';
    const DEFAULT_NOTIFICATION_STATUS = '';
    const DEFAULT_NOTIFICATION_TYPE = 'App\Services\NotificationService';

    /** @var  NotificationTemplate */
    private $_template;

    /** @var  Notification */
    private $_notification;

    public function __construct()
    {
        $this->_template = new NotificationTemplate();
        $this->_notification = new Notification();
    }

    /**
     * @param Tags $tags
     * @param string $text
     *
     * @return string
     */
    private function createNotificationText(Tags $tags, $text)
    {
        preg_match_all('|\[\[(.*?)\]\]|i', $text, $arr);
        if(!empty($arr[1])){
            foreach($arr[1] as $tag){
                $method_name = 'get_'.$tag;
                $replace_tag = '[['.$tag.']]';
                if(method_exists($tags, $method_name)){
                    $text = str_replace($replace_tag, $tags->$method_name(), $text);
                } elseif ($value = $tags->get($tag)){
                    $text = str_replace($replace_tag, $value, $text);
                } else {
                    $text = str_replace($replace_tag, '', $text);
                }
            }
        }
        return $text;
    }

    /**
     * @param Tags $tags
     * @param string $alias
     * @param string $user_id
     * @param string $lang
     *
     * @return string
     */
    public function create(Tags $tags, $alias,  $user_id, $lang)
    {
        try {
            $lang = !empty($lang) ? $lang : self::DEFAULT_LANGUAGE;
            $tmp = $this->_template->getTemplate($alias, $lang);
            $errors = $this->_setErrors([
                'template' => $tmp,
                'user_id' => $user_id,
                'alias' => $alias
            ]);
            if(!empty($errors)){throw new \Exception($errors);}
            $this->_notification->user_id = $user_id;
            $this->_notification->title = $tmp->title;
            $this->_notification->text = $this->createNotificationText($tags, $tmp->description);
            $this->_notification->alias = $tmp->alias;
            $this->_notification->notificationable_type = self::DEFAULT_NOTIFICATION_TYPE;
            $this->_notification->status = self::DEFAULT_NOTIFICATION_STATUS;
            $this->_notification->sended_at = null; //sended_at ставится время в commands\Notify.php
            $this->_notification->save();
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    /**
     * @param array $params
     *
     * @return string
     */
    private function _setErrors($params = [])
    {
        $errors = null;
        foreach($params as $name=>$param){
            if(empty($param)){
                $errors = (string)$errors . 'Parameter - '.$name.' is empty. ';
            }
        }
        return $errors;
    }
}