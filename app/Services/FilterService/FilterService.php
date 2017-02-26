<?php

namespace App\Services\FilterService;

use Illuminate\Database\Eloquent\Model;

class FilterService
{
    const TEXT_TYPE = 'text';
    const DATE_LIKE_TYPE = 'datelike';
    const DATE_TYPE = 'date';
    const PRICE_TYPE = 'price';
    const NAME_DELIMETER = '::';
    const NAME_FILTER = 'filter';
    const NAME_COUNT = 4;

    /** @var  array */
    private $_filters = [];

    /** @var  string */
    private $_table;

    /** @var  Field */
    private $_field;

    public function __construct($table)
    {
        $this->_table = $table;
        $this->_field = new Field();
    }

    /**
     * @param string $table
     * @param string $field
     * @param string $label
     * @param string $type
     * @param string|integer|float $value
     */
    public function setTextField($table, $field, $label, $type = self::TEXT_TYPE, $value = null)
    {
        $this->_filters[] = $this->_field->createTextField($this->_createFilterName($table, $field, $type), $label,
            $value);
    }

    /**
     * @param string $table
     * @param string $field
     * @param string $label
     * @param string $type
     * @param array $value
     */
    public function setListField($table, $field, $label, $type = self::TEXT_TYPE, $value = null)
    {
        $this->_filters[] = $this->_field->createListField($this->_createFilterName($table, $field, $type), $label,
            $value);
    }

    /**
     * @param string $table
     * @param string $field
     * @param string $label
     * @param string $type
     * @param array $value
     */
    public function setPeriodField($table, $field, $label, $type = self::TEXT_TYPE, $value = null)
    {
        $this->_filters[] = $this->_field->createPeriodField($this->_createFilterName($table, $field, $type), $label,
            $value);
    }

    /**
     * @param string $table
     * @param string $field
     * @param string $type
     *
     * @return string
     */
    private function _createFilterName($table, $field, $type)
    {
        return self::NAME_FILTER . self::NAME_DELIMETER . $table . self::NAME_DELIMETER . $field . self::NAME_DELIMETER . $type;
    }

    /**
     * @param string $listname
     * @param string $url
     *
     * @return string
     */
    public function create($listname, $url)
    {
        return !empty($listname) && !empty($url) ? $this->_field->create($this->_filters, $listname, $url) : null;
    }

    /**
     * @param object $model
     * @param array $filters
     *
     * @return Model
     */
    public function createFilterRequest($model, $filters = [])
    {
        foreach ($filters as $filter => $value) {
            $data = explode(self::NAME_DELIMETER, $filter);
            if (is_array($data) && count($data) == self::NAME_COUNT && $data[0] == self::NAME_FILTER && !empty($value)) {
                if (!is_array($value)) {
                    if ($data[3] == self::DATE_LIKE_TYPE) {
                        $model = $this->_createRequest($model, $data[1], $data[2],
                            date('Y-m-d', strtotime($value)) . '%', 'LIKE');
                    } else {
                        $model = $this->_createRequest($model, $data[1], $data[2],
                            $this->_checkTypeOfValue($data[3], $value), '=');
                    }
                } else {
                    $model = $this->_createRequest($model, $data[1], $data[2], $this->_checkTypeOfValue($data[3], $value['from']), '>=');
                    $model = $this->_createRequest($model, $data[1], $data[2], $this->_checkTypeOfValue($data[3], $value['to']), '<=');
                }
            }
        }
        return $model;
    }

    /**
     * @param string $type
     * @param string $value
     *
     * @return string
     */
    private function _checkTypeOfValue($type, $value)
    {
        if ($type == self::PRICE_TYPE) {
            $value = $value * 100;
        } elseif ($type == self::DATE_TYPE && !empty($value)) {
            $value = date('Y-m-d H:i:s', strtotime($value));
        }
        return $value;
    }

    /**
     * @param mixed $model
     * @param string|array $table
     * @param string $field
     * @param string $value
     * @param string $operator
     *
     * @return mixed
     */
    private function _createRequest($model, $table, $field, $value, $operator)
    {
        if (!empty($value)) {
            if ($table == $this->_table) {
                $model->where($field, $operator, $value);
            } else {
                $tables = explode(',', $table);
                foreach ($tables as $tbl) {
                    $model = $model->with($tbl);
                }
                $request = 'whereHas';
                foreach ($tables as $tbl) {
                    $model->$request($tbl, function ($query) use ($field, $operator, $value) {
                        $query->where($field, $operator, $value);
                    });
                    $request = 'orWhereHas';
                }

            }
        }
        return $model;
    }
}