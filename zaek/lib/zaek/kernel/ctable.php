<?php
namespace zaek\kernel;

use zaek\basics\graph\CNode;


/**
 * CTable - класс для работы с данными, которые можно представить в виде таблицы
 *
 * Пример использования:
 *
 * $table = new CTable(array(
 *      array(1,'root'),
 *      array(2,'guest')
 * ));
 * $table->setNames(array('id', 'login'));
 *
 * vd($table->fetch($table::FETCH_ASSOC));
 * vd($table->fetch());
 * vd($table->fetch());
 */
class CTable
{
    /**
     * @const MODE_DIRECT
     */
    const MODE_DIRECT  = 1;
    const MODE_REVERSE = 2;

    const FETCH_ASSOC = 1;
    const FETCH_NUM   = 2;

    /**
     * @var array $_data Массив данных
     */
    protected $_data = array();
    /**
     * @var int $_column Текущее положение указателя колонки
     */
    protected $_column = 1;
    /**
     * @var int $_row Текущее положение указателя строки
     */
    protected $_row = 0;
    /**
     * @var int $_row Текущая строка в fetchByKey
     */
    protected $_row_by_key = 0;
    /**
     * @var int $_width Ширина текущей матрицы
     */
    protected $_width = 0;
    /**
     * @var int $_height Высота текущей матрицы
     */
    protected $_height = 0;
    /**
     * Массив заголовков столбцов
     * @var array
     */
    protected $_keys = array();
    /**
     *
     * @var array
     */
    protected $_columns = array();
    /**
     * Ассоциативность массива
     * @var boolean
     */
    protected $_assoc = false;

    protected $_cur_page = 1;
    protected $_per_page = 0;
    protected $_cnt_page = 0;
    protected $_top_limit = 0;
    protected $_low_limit = 0;

    /**
     * fillFromArray устанавливает входной массив в качестве источника данных
     * для объекта
     *
     * @param array $arr Входной массив
     * @return CTable
     */
    public function fillFromArray($arr)
    {
        if(is_array($arr)) {
            $this->_data = array_values($arr);
            $this->_height = count($arr);
            $this->_top_limit = count($arr);
            $this->_width = count(array_shift($arr));
        }
        return $this;
    }
    /**
     * Добавляет строку в набор данных
     * @param array $arr
     * @return \zaek\kernel\CTable
     */
    public function addRow($arr)
    {
        $this->_data[] = $arr;
        $this->setHeight(++$this->_height);
        return $this;
    }

    /**
     * Устанавливает заголовки столбцов
     *
     * @param array $arr
     * @return CTable
     * @throws CException
     */
    public function setNames($arr)
    {
        if($this->_width == count($arr) || $this->_width == 0) {
            $this->_keys = $arr;
            $this->_width = count($arr);
        } else {
            throw new CException('COLUMN_COUNT_DOES_NOT_MATCH ['.$this->_width.', '.count($arr).']');
        }

        return $this;
    }
    /**
     * row Возвращает массив данных для выбранной строки
     *
     * @param int $r Номер строки
     * @return array|boolean Возвращает массив, содержащий строку данных,
     * либо false, если заданная строка отсутствует
     */
    public function row($r)
    {
        if($r < 1) return false;

        if($this->_height >= $r) {
            return $this->_data[$r - 1];
        } else {
            return false;
        }
    }
    /**
     * column Возвращает массив, содержащий столбец данных
     *
     * @param int $c Название колонки
     * @return mixed Возвращает массив, содержащий столбец данных, либо
     * false, если запрашиваемый столбец отсутствует
     */
    public function column($c)
    {
        // Запоминаем текущее положение курсора и обходим строки данных,
        // заполняя массив колонки. Если ключ числовой, то находим строковое
        // представление

        if(!is_numeric($c) && count($this->_keys) > 0) {
            // функция array_search находит похожие варианты, не прокатывает
            // для поиска имени колонки
            for($i=0; $i<count($this->_keys); $i++)
                if($this->_keys[$i] == $c) {
                    $c = $i;
                    break;
                }
            // найдём колонку - будет числом
            if(!is_numeric($c)) return false;

        } elseif(is_numeric($c)) {
            $c--;
            if($c >= $this->_width) return false;
        }

        if(array_key_exists($c, $this->_columns)) return $this->_columns[$c];

        $tmp = $this->_row;
        $this->_row = 0;

        while($row = $this->fetch())
            $this->_columns[$c][] = $row[$c];

        $this->_row = $tmp;

        return $this->_columns[$c];
    }

    /**
     * fetch Возвращает текущую строку данных и перемещает указатель строки
     * на одну позицию вперёд, в случае, если указатель вышел за пределы
     * данных - возвращает false
     *
     * @param int $mode Тип возвращаемого массива
     * @return mixed Массив текущей строки, либо false
     */
    public function fetch($mode = self::FETCH_NUM)
    {
        if ( $this->_row < $this->_low_limit ) {
            $this->_row = $this->_low_limit;
        }

        if($this->_row < $this->_top_limit ) {
            switch($mode) {
                case self::FETCH_ASSOC:
                    return array_combine($this->_keys, $this->_data[$this->_row++]);
                    break;
                default:
                    return $this->_data[$this->_row++];
                    break;
            }
        } else {
            return false;
        }
    }

    /**
     * Выполняет действия, аналогичные методу fetch, вызывая при этом
     * функцию $func для обработки текущей строки, в качестве параметра
     * callback-функции передаётся ссылка на текущую выборку
     *
     * @param $func Callback-функция для обработки результата выбора
     * @param int $mode Тип возвращаемого массива
     * @return mixed
     */
    public function fetchCb($func, $mode = self::FETCH_NUM)
    {
        $tmp = $this->fetch($mode);
        if($tmp != false) {
            call_user_func($func, $tmp);
        }

        return $tmp;

    }
    /**
     * fetchColumn Вовзвращает текущую колонку данных и перемещает внутренний
     * указатель колонки на позицию вперёд, в случае, если указатель вышел за
     * пределы данных - возвращает false
     *
     * @return mixed Массив текущей колонки, либо false
     */
    public function fetchColumn()
    {
        return ($this->_column <= $this->_width) ? $this->column($this->_column++): false;
    }

    /**
     * Возвращает количество строк в наборе данных
     * @return int
     */
    public function getLength()
    {
        return $this->_height;
    }
    /**
     *
     * @param int $i
     * @return \zaek\kernel\CTable
     */
    public function setRow($i)
    {
        if($i < $this->_height) $this->_row = $i;
        return $this;
    }

    /**
     * Преобразовывает данные в массив, параметр (CQueryField)$field используется для создания ключей массива,
     * установка флага bMulti в значение true означает, что поле, по которому будет происходить отбор
     * неуникальное.
     *
     * @param CNode $field
     * @param bool $bMulti
     * @return array
     */
    public function toArray(CNode $field = null, $bMulti = false)
    {
        // Без отбора по колонке
        if($field == null) {
            return $this->_data;
        } else {
            // Индекс отбираемого значения
            $cell = array_search($field->getId(), $this->_keys);
            $aReturn = array();
            // Неуникальный код
            if($bMulti) {
                for($i = 0; $i < count($this->_data); $i++) {
                    $aReturn[$this->_data[$i][$cell]][] = array_combine($this->_keys, $this->_data[$i]);
                }
                // Уникальный код
            } else {
                for($i = 0; $i < count($this->_data); $i++) {
                    $aReturn[$this->_data[$i][$cell]] = array_combine($this->_keys, $this->_data[$i]);
                }
            }
            return $aReturn;
        }
    }
    public function toAssoc(CNode $field = null, $bMulti = false)
    {
        $aReturn = array();

        if($bMulti && $field !== null) {
            $r = $this->toArray($field, $bMulti);

            foreach($r as $k1 => $arr) {
                $aReturn[$k1] = array();

                foreach($arr as $k2 => $v) {
                    $aReturn[$k1][$k2] = $v;
                }
            }
        } else {
            if ( $field ) {
                $r = $this->toArray($field);
                return $r;
            } else {
                for($i = 0; $i < count($this->_data); $i++) {
                    $aReturn[$i] = array_combine($this->_keys, $this->_data[$i]);
                }
            }
        }
        return $aReturn;
    }

    /**
     * Возвращает текущую строку
     * @param bool $i
     * @return mixed
     */
    public function current($i = false)
    {
        if($this->_row == $this->_height) {
            return false;
        } else {
            return ($i !== false)
                ?
                ((array_key_exists($i, $this->_data[$this->_row])) ? $this->_data[$this->_row][$i] : false)
                : $this->_data[$this->_row];
        }
    }
    /**
     * Show as table
     */
    public function show()
    {
        echo "<table border='1'>";
        $this->toArray();
        echo "<thead><tr>";
        for($i = 0; $i < count($this->_keys); $i++) {
            echo "<th>".$this->_keys[$i]."</th>";
        }
        echo "</tr></thead><tbody>";

        for($i=0; $i< $this->_height; $i++) {
            echo "<tr>";

            for($j = 0; $j < count($this->_keys); $j++) {
                echo "<td>".strval($this->_data[$i][$j])."</td>";
            }

            echo "</tr>";
        }

        echo "</tbody></table>";
    }
    public function getHeader()
    {
        return $this->_keys;
    }
    public function flush()
    {
        $this->_row = 0;
    }
    public function __toString()
    {
        return __CLASS__ . "[".$this->_width.":".$this->_height."]";
    }

    /**
     * @param CNode $field - UNIQUE KEY CELL
     * @param array $aMulti
     * @param int $type
     * @param bool $int
     * @return array
     * @throws CException
     */
    public function fetchByKey(CNode $field, array $aMulti = array(), $type = self::FETCH_NUM, $int = false)
    {
        $cell = array_search($field->getCode(), $this->_keys);
        $arr = array();
        foreach($aMulti as $f) {
            if(($i = array_search($f->getCode(), $this->_keys)) !== false)
                $arr[] =  $i;
        }
        $aReturn = false;
        // Неуникальный код
        $key = false;

        $bFirst = false;
        /** */
        if(!$int) {
            while($this->_row_by_key < $this->_low_limit) {
                for ($i = $this->_row; $i < count($this->_data); $i++) {
                    if($this->_data[$i][$cell] == $key) {
                        if($type == self::FETCH_ASSOC) {
                            foreach ($arr as $index) { // $index - индекс в массиве
                                $aReturn[$this->_keys[$index]][] = $this->_data[$i][$index];
                            }
                        } else {
                            foreach ($arr as $index) { // $index - индекс в массиве
                                $aReturn[$index][] = $this->_data[$i][$index];
                            }
                        }
                    } else {
                        if($bFirst) {
                            break;
                        } else {
                            $bFirst = true;
                        }
                        if($type == self::FETCH_ASSOC) {
                            $aReturn = array_combine($this->_keys, $this->_data[$this->_row++]);
                            foreach($arr as $index) { // $index - индекс в массиве
                                $aReturn[$this->_keys[$index]] = array($this->_data[$i][$index]);
                            }
                        } else {
                            $aReturn = $this->_data[$i];
                            foreach($arr as $index) { // $index - индекс в массиве
                                $aReturn[$index] = array($this->_data[$i][$index]);
                            }
                        }

                        $key = $this->_data[$i][$cell];

                    }
                }
                $this->_row = $i;
                $this->_row_by_key++;
            }
        }
        /** */
        if($this->_row_by_key < $this->_top_limit ) {
            for ($i = $this->_row; $i < count($this->_data); $i++) {
                if($this->_data[$i][$cell] == $key) {
                    if($type == self::FETCH_ASSOC) {
                        foreach ($arr as $index) { // $index - индекс в массиве
                            $aReturn[$this->_keys[$index]][] = $this->_data[$i][$index];
                        }
                    } else {
                        foreach ($arr as $index) { // $index - индекс в массиве
                            $aReturn[$index][] = $this->_data[$i][$index];
                        }
                    }
                } else {
                    if($bFirst) {
                        break;
                    } else {
                        $bFirst = true;
                    }
                    if($type == self::FETCH_ASSOC) {
                        $aReturn = @array_combine($this->_keys, $this->_data[$this->_row++]);

                        if(!$aReturn) {
                            $aError = error_get_last();
                            if($aError && $aError['line'] == __LINE__ - 4) {
                                throw new CException('COLUMN_COUNT_DOES_NOT_MATCH');
                            }
                        }


                        foreach($arr as $index) { // $index - индекс в массиве
                            $aReturn[$this->_keys[$index]] = array($this->_data[$i][$index]);
                        }
                    } else {
                        $aReturn = $this->_data[$i];
                        foreach($arr as $index) { // $index - индекс в массиве
                            $aReturn[$index] = array($this->_data[$i][$index]);
                        }
                    }

                    $key = $this->_data[$i][$cell];
                }
            }

            $this->_row = $i;
            $this->_row_by_key++;

            return $aReturn;
        } else {
            return false;
        }
    }
    private function _calc()
    {
        if($this->_per_page && $this->_height) {
            $this->_cnt_page = ceil($this->_height / $this->_per_page);
        } else {
            $this->_per_page = $this->_height;
        }

        if($this->_cur_page && $this->_per_page) {
            // $this->_row = $this->_per_page * ($this->_cur_page-1);
            $this->_low_limit = $this->_per_page * ($this->_cur_page-1);

            $this->_top_limit = $this->_per_page * $this->_cur_page;
            if($this->_top_limit > $this->_height) {
                $this->_top_limit = $this->_height;
            }
        }
    }
    public function setHeight($h)
    {
        $this->_height = $h;
        $this->_top_limit = $h;
        $this->_calc();
    }
    public function setPageSize($i)
    {
        if(($i = intval($i)) >= 0)
            $this->_per_page = $i;

        $this->_calc();
    }
    public function setPageNumber($i)
    {
        if(($i = intval($i)) > 0) {
            $this->_cur_page = $i;
        }

        $this->_calc();
    }
    public function getPageCount()
    {
        return $this->_cnt_page;
    }
    public function getCurrentPage()
    {
        return $this->_cur_page;
    }
}