<?php

namespace App\Services\FilterService;

final class Field
{
    /**
     * @param string $label
     * @param string $name
     *
     * @return string
     */
    public function createTextField($name, $label, $value = null)
    {
        $field = '<label class="col-md-1 control-label">'.$label.'</label>';
        $field .= '<div class="col-md-2">';
        $field .= '<input class="form-control" type="text" name="' . $name . '"';
        $field .= ' placeholder="' . $label . '" value="' . $value . '" >';
        $field .= '</div>';
        return  $field;
    }

    /**
     * @param string $label
     * @param string $name
     * @param array $value
     *
     * @return string
     */
    public function createListField($name, $label, $value = [])
    {
        $field = '<label class="col-md-1 control-label">'.$label.'</label><div class="col-md-2">';
        $field .= '<select name="'.$name.'" class="form-control classifier-selector change-listener-ready">';
        $field .= '<option></option>';
        foreach ($value as $key => $value){
            $field .= '<option value="'.$key.'">'.$value.'</option>';
        }
        $field .= '</select></div>';
        return  $field;
    }

    /**
     * @param string $label
     * @param string $name
     * @param array $value
     *
     * @return string
     */
    public function createPeriodField($name, $label, $value = [])
    {
        $from = 'value="'.!empty($value[0]) ? $value[0] : null.'"';
        $to = 'value="'.!empty($value[1]) ? $value[1] : null.'"';
        $field = '<label class="col-md-1 control-label">'.$label.'</label>';
        $field .= '<div class="col-md-2"><input class="form-control" placeholder="З" type="text" name="'.$name.'[from]" '.$from.'>';
        $field .= '<input class="form-control" placeholder="До" type="text" name="'.$name.'[to]" '.$to.'></div>';
        return  $field;
    }

    /**
     * @param array $filters
     *
     * @return string
     */
    public function create($filters, $listname, $url)
    {

        $filterItem = '';
        $filtersBlock = '';
        $i = 0;
        foreach($filters as $k=>$filter)
        {
            $filterItem .= $filter;
            ++$i;
            if($i > 2 || $k == count($filters)-1) {
                $filtersBlock .= $this->_createFiltersBlock($filterItem);
                $filterItem = '';
                $i = 0;
            }
        }
        $this->_script();
        return $this->_createFiltersForm($filtersBlock, $listname, $url);
    }

    /**
     * @param string $filters
     * @param  string $listName
     * @param  string $filterUrl
     *
     * @return string
     */
    private function _createFiltersForm($filters, $listName, $filterUrl)
    {
        $button = '<div class="form-group "><div class="pull-right col-xs-3">
        <input type="button" class="btn btn-success filter-button" onclick="filter(\''.$listName.'\', \''.$filterUrl.'\')" value="Фільтрувати">
        <input type="button" class="btn btn-warning filter-cancel-button" onclick="cancelFilter(\''.$listName.'\', \''.$filterUrl.'\')" value="Відмінити"</div></div>';
        return "<div class='well'><form class='form-horizontal' id='filter-form' method='POST'>".$filters.$button."</form></div>";
    }

    /**
     * @param string $filters
     *
     * @return string
     */
    private function _createFiltersBlock($filters)
    {
        return "<div class='form-group'>".$filters."</div>";
    }


    /**
     * @return mixed
     */
    private function _script()
    {
        echo '<script src="/js/filter.js"></script>';
    }

}