<?php
/**
 * Загрузка массива слов, из которые меняя одну букву в начальном слове, приходи к конечному слову.
 * Результат в виде массива.
 */
class SearchWord {
    
    private $arr_words = [];
    private $start_word = "";
    private $finish_word = "";
    private $tree = [];
    private $errors = [];

    function __construct($arr_words, $start_word, $finish_word) {
        $this->arr_words = $arr_words;
        $this->start_word = $start_word;
        $this->finish_word = $finish_word;
    }

    /**
     * Инициализируем начальные данные и запускаем создание дерева
     */
    public function start_search() {
        if(mb_strlen($this->start_word) != mb_strlen($this->finish_word)) {
            $this->errors[] = "В начальном и конечном слове кол-во символов не совпадает";  
        }
        if(count($this->errors)==0) {
            $this->tree[0][0][] = $this->start_word;
            $this->delete_words($this->tree[0][0]);
            $this->search($this->start_word, 1, 0);
        } else {
            foreach($this->errors as $v)echo$v;
        }
    }

    /**
     * Создаём дерево,
     * @param string $input Слово, для поиска следующих слов
     * @param integer $step Узел, Шаг. 
     * @param integer $key_parent Ключ потомка
     * @return void
     */
    private function search($input, $step, $key_parent) {
        //конвертируем слово
        $arrstr_input = $this->str_split_unicode($input);
        //Создаём регулярные выражения, находим все слова отличающейся на одну букву
        for($a=0, $b=count($arrstr_input); $a<$b; $a++) {
            $pregmatch_input[$a] = "";
            $pregmatch_input[$a] .= "/";
            foreach($arrstr_input as $key => $v) {
                if($key == $a) {
                    $pregmatch_input[$a] .= "[а-я]";
                } else {
                    $pregmatch_input[$a] .= $v;
                }
            }
            $pregmatch_input[$a] .= "/ui";
        }
        $leaf = [];
        $branch = [];        
        foreach($pregmatch_input as $val) { //перечисляем регулярные выражения
            if (count($res = preg_grep($val, $this->arr_words)) != 0) { //ищем в общем дереве слова совподающие с регулярным выражением
                foreach($res as $k => $v) {
                    if($v == $this->finish_word) {
                        $this->tree[$step][$key_parent][] = $v;//Нашли последнее слово
                        $leaf[] = $v;
                    } else {
                        $this->tree[$step][$key_parent][] = $v;//Записавыем ветку в дерево, шаг, ключ от родительского слова, само слово
                        $branch[$key_parent][] = $v;
                    }
                }
            } else {
                continue;
            }
        }
        if(!empty($leaf)) {// прекращаем поиск
            return TRUE;
        } elseif(!empty($branch)) {//продолжаем поиск по веткам, пока не найдём конечную точку, либо не найдём.
            foreach($branch as $v) {
                $this->delete_words($v);//удаляем слова которые прошли
                foreach($v as $k => $val) {
                    $this->search($val, $step+1, $k);
                }
            }
        } else {
            return FALSE;
        }
    }

    /**
     * Удаляем в дереве пройденые слова
     *
     * @param [type] $arr одномерный массив
     * @return void
     */
    private function delete_words($arr) {
        $ar = array_intersect($this->arr_words, $arr);
        if(!empty($ar)) {
            foreach($ar as $v) {
                $key = array_search($v, $this->arr_words);
                unset($this->arr_words[$key]);
            }
        }
    }

    /**
     * Возвращаем результат выполения.
     *
     * @return array
     */
    public function getResult() {
        if(count($this->tree)!=0) {
            $parent_key = 0;
            for($a=count($this->tree)-1; $a>=0; $a--) {
                foreach($this->tree[$a] as $key=>$v) {
                    $result[] = $v[$parent_key];
                    $parent_key = $key;
                }          
            }
            return array_reverse($result);
        } else {
            return [];
        }
    }

    /**
     * Конвертация в UTF-8.
     *
     * @param [type] $str
     * @param integer $l
     * @return string
     */
    private function str_split_unicode($str, $l = 0) {
        if ($l > 0) {
            $ret = [];
            $len = mb_strlen($str, "UTF-8");
            for ($i = 0; $i < $len; $i += $l)$ret[] = mb_substr($str, $i, $l, "UTF-8");
            return $ret;
        }
        return preg_split("//u", $str, -1, PREG_SPLIT_NO_EMPTY);
    }
}

$lines = file('txt.txt',FILE_IGNORE_NEW_LINES);
$obj = new SearchWord($lines , "лужа", "море");
$obj->start_search();
$result = $obj->getResult();
foreach($result as $key => $v) {
    if($key!=0)echo'->';
    echo$v;
}