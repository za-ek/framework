<?php
namespace Zaek\Data\Mysqli;

use Zaek\Kernel\Table;

class Result extends Table
{
    private $_mysqli_result;
    public function setMysqliResult(\mysqli_result $result)
    {
        $this->_mysqli_result = $result;


        $aFields = mysqli_fetch_fields($result);
        $aNames = [];
        foreach ($aFields as $field) {
            $aNames[] = $field->name;
        }
        $this->setNames($aNames);


        $aData = [];
        while ($row = mysqli_fetch_array($result, MYSQLI_NUM)) {
            $aData[] = $row;
        }

        $this->_data = $aData;

        $this->setHeight(count($this->_data));
    }
}