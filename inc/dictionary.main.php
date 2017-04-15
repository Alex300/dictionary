<?php
defined('COT_CODE') or die('Wrong URL');

/**
 * Main Controller class for Dictionary plugin
 * 
 * @package Dictionary
 * @author Kalnov Alexey <kalnovalexey@yandex.ru>
 * @copyright Portal30 Studio http://portal30.ru
 */
class MainController
{
    /**
     * Ajax
     * Получаем значения для зависимых словарей
     */
    public function ajxGetChildValuesAction(){
        global $db_pages;

        $ret = array('error' => '', 'message' => "", 'data' => array());

        $childs = cot_import('childs', 'P', 'ARR');
        $existOnly = cot_import('ex_only', 'P', 'INT', 1);

        if(empty($childs)){
            $ret['error'] = cot::$L['dict_not_found'];
            echo json_encode($ret);
            exit;
        }

        if(is_array($childs)){
            foreach($childs as $key => $childRow){
                $id = $childRow['id'];
                $childs[$key]['dic_id'] = $childRow['dic_id'] = (int)$childs[$key]['dic_id'];

                if(!$childRow['dic_id']){
                    unset($childs[$key]);
                    continue;
                }

                // Включаем пустое значение
                $ret['data'][$id]['_0_'] = cot::$R['code_option_empty'];

                $condition = array(
                    array('dictionary', $childRow['dic_id']),
                );
                $childRow['parent1_value'] = (int)$childRow['parent1_value'];
                $childRow['parent2_value'] = (int)$childRow['parent2_value'];
                if(!empty($childRow['parent1_value'])) $condition[] = array('parent', $childRow['parent1_value']);
                if(!empty($childRow['parent2_value'])) $condition[] = array('parent2', $childRow['parent2_value']);

                $tmp = dictionary_model_Value::keyValPairs($condition);
                if(!empty($tmp)) {
                    foreach($tmp as $valId => $valTitle) {
                        $ret['data'][$id]['_'.$valId.'_'] = $valTitle;
                    }
                    unset($tmp);
                }
            }
        }


        echo json_encode($ret);
        exit;
    }

    public function __old__ajxGetChildValuesAction(){
        global $db_pages;

        $ret = array('error' => '', 'message' => "", 'data' => array());

        $parent_dicId = cot_import('parent_dicId', 'P', 'INT');
        $parent_dicValue = cot_import('parent_dicValue', 'P', 'INT');
        $childs = cot_import('childs', 'P', 'ARR');
        $existOnly = cot_import('ex_only', 'P', 'INT', 1);

        if (!$parent_dicId){
            $ret['error'] = cot::$L['dict_not_found'];
            echo json_encode($ret);
        }
        if(is_array($childs)){
            foreach($childs as $key => $val){
                $childs[$key] = (int)$childs[$key];
                if(!$childs[$key]){
                    unset($childs[$key]);
                    continue;
                }

                // Включаем пустое значение
                $ret['data'][$childs[$key]][0] = cot::$R['code_option_empty'];
            }
        }else{
            // Можно получить потомков из базы
        }
        if(count($childs) == 0){
            $ret['error'] = cot::$L['dict_not_found'];
            echo json_encode($ret);
            exit;
        }
        // Получаем нужные значения
        $condition = array(
            array('dictionary', $childs)
        );

        // parent2

        if (!empty($parent_dicValue)){
            $condition[] = array('parent', $parent_dicValue);
            $dic_values = dictionary_model_Value::findByCondition($condition, 0, 0, array(array('value', 'ASC')));
        }

        if(empty($dic_values)) {
            $dic_values = array();
        }


        // Если необходимо, то берем только существующие значения
        // @todo возможно учесть только опубликованные страницы
//        if ($existOnly){
//            $valIds = array();
//            $existValues = array();
//            foreach($dic_values as $dic_value){
//                $valIds[$dic_value['field_location']][$dic_value["field_name"]][] = $dic_value['dv_id'];
//            }
//            if (count($valIds[$db_pages]) > 0){
//                foreach($valIds[$db_pages] as $field => $ids){
//                    if (count($ids) > 0){
//                        $sql = "SELECT DISTINCT page_{$field} as exval FROM {$db_pages}
//                                   WHERE page_{$field} IN (".implode(',', $ids).")";
//                        $existValues[$db_pages][$field] = $db->query($sql)->fetchAll(PDO::FETCH_COLUMN, 0);
//                    }
//                }
//            }
//        }

        reset($dic_values);

        if (count($dic_values) == 0){
            echo json_encode($ret);
            exit;
        }

        // Разбираем по словарям
        foreach($dic_values as $dic_value){
            // Включаем пустое значение
            $dicId = $dic_value->rawValue('dictionary');
            if (empty($ret['data'][$dicId][0])) $ret['data'][$dicId][0] = cot::$R['code_option_empty'];
//            if ($existOnly){
//                if (empty($existValues[$fielsLoc][$fieldName]) || !in_array($variant, $existValues[$fielsLoc][$fieldName])){
//                    continue;
//                }
//            }
            $ret['data'][$dicId][$dic_value->id] = $dic_value->value;
        }
        echo json_encode($ret);
        exit;
    }

}