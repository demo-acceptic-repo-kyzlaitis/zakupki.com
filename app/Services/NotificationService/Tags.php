<?php

namespace App\Services\NotificationService;

class Tags
{
    /** @var  string */
    private $_tender_link;

    /** @var  string */
    private $_tender_name;

    /** @var  string */
    private $_tender_date;

    /** @var  string */
    private $_offers_link;

    /** @var  string */
    private $_claim_link;

    /** @var  string */
    private $_balance_link;

    /** @var  string */
    private $_balance_sum;

    /** @var  string */
    private $_organization_link;

    /** @var  string */
    private $_organization_name;

    /** @var  string */
    private $_organization_address;

    /** @var  string */
    private $_plans_link;

    /** @var  string */
    private $_text;

    /** @var array */
    private $_tags = [];

    /**
     * @param string $key
     * @param string $value
     */
    public function set($key, $value)
    {
        $this->_tags[$key] = $value;
    }

    /**
     * @param string $key
     * @return string|null
     */
    public function get($key)
    {
        return !empty($this->_tags[$key]) ? $this->_tags[$key] : null;
    }

    /**
     * @param string $link
     */
    public function set_tender_link($link)
    {
        $this->_tender_link = $link;
    }

    /**
     * @param string $name
     */
    public function set_tender_name($name)
    {
        $this->_tender_name = $name;
    }

    /**
     * @param string $name
     */
    public function set_organization_name($name)
    {
        $this->_organization_name = $name;
    }


    /**
     * @param string $address
     */
    public function set_organization_address($address)
    {
        $this->_organization_address = $address;
    }

    /**
     * @param string $date
     */
    public function set_tender_date($date)
    {
        $this->_tender_date = $date;
    }

    /**
     * @param string $link
     */
    public function set_offers_link($link)
    {
        $this->_offers_link = $link;
    }

    /**
     * @param string $link
     */
    public function set_claim_link($link)
    {
        $this->_claim_link = $link;
    }

    /**
     * @param string $link
     */
    public function set_balance_link($link)
    {
        $this->_balance_link = $link;
    }

    /**
     * @param string $link
     */
    public function set_organization_link($link)
    {
        $this->_organization_link = $link;
    }

    /**
     * @param string $sum
     */
    public function set_balance_sum($sum)
    {
        $this->_balance_sum = $sum;
    }

    /**
     * @param string $link
     */
    public function set_plans_link($link)
    {
        $this->_plans_link = $link;
    }

    /**
     * @param string $text
     */
    public function set_text($text)
    {
        $this->_text = $text;
    }

    /**
     * @return string
     */
    public function get_text()
    {
        return $this->_text;
    }

    /**
     * @return string
     */
    public function get_tender_link()
    {
        return $this->_tender_link;
    }

    /**
     * @return string
     */
    public function get_tender_name()
    {
        return $this->_tender_name;
    }

    /**
     * @return string
     */
    public function get_tender_date()
    {
        return $this->_tender_date;
    }

    /**
     * @return string
     */
    public function get_offers_link()
    {
        return $this->_offers_link;
    }

    /**
     * @return string
     */
    public function get_claim_link()
    {
        return $this->_claim_link;
    }

    /**
     * @return string
     */
    public function get_balance_link()
    {
        return $this->_balance_link;
    }

    /**
     * @return string
     */
    public function get_organization_link()
    {
        return $this->_organization_link;
    }

    /**
     * @return string
     */
    public function get_organization_name()
    {
        return $this->_organization_name;
    }

    /**
     * @return string
     */
    public function get_organization_address()
    {
        return $this->_organization_address;
    }

    /**
     * @return string
     */
    public function get_balance_sum()
    {
        return $this->_balance_sum;
    }

    /**
     * @return string
     */
    public function get_plans_link()
    {
        return $this->_plans_link;
    }

}