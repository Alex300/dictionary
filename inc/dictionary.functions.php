<?php
/**
 * Dictionary plugin for Cotonti Siena
 *
 * @package Dictionary
 * @author Kalnov Alexey <kalnovalexey@yandex.ru>
 * @copyright Portal30 Studio http://portal30.ru
 */
defined('COT_CODE') or die('Wrong URL');

// Lang file
global $L;
require_once cot_langfile('dictionary', 'plug');

cot::$db->registerTable('dictionary');
cot::$db->registerTable('dictionary_values');

/**
 * Загружает данные словарей с привязкой к экстрапоям
 *
 * @param array $config Конфигурация:
 *
 * Пример конфигурации:
 * $cnf = array(
 *   array(
 *      'location' => cot::$db->advert,     // Таблица с экстраполем
 *      'field' => 'brand',                 // Поле
 *      'dictionary' => 2                   // id Словаря
 *   ),
 *   array(
 *      'location' => 'cot_advert',         // Можно просто строкой
 *      'field' => 'model',
 *      'dictionary' => 3,
 *      'rc' => '<select name="{$name}"{$attrs}>{$options}</select>{$error}', // Переопределение строки рессурса
 *      'parent' => $advert->brand          // Значение родительского словаря
 *   )
 * );
 *
 */
function dic_loadExtraFieldData($config) {
    global $cot_extrafields;

    // Грузим все корневые значения
    $rootIds = $dicIds = array();

    foreach($config as $field) {
        $dicIds[] = $field['dictionary'];
    }
    reset($config);

    $dictionaries = dictionary_model_Dictionary::find(array(array('id', $dicIds)));
    if(!$dictionaries) return;

    foreach($dictionaries as $dicRow) {
        $parent = $dicRow->rawValue('parent');
        if($parent > 0 && isset($dictionaries[$parent])) {
            $dictionaries[$parent]->hasChilds = true;

        } else {
            $rootIds[$dicRow->id] = $dicRow->id;
        }
    }

    $orWhere = array();

    foreach($config as $field) {
        //if(empty($field['parent']) && empty($field['parent2'])) $rootIds[] = $field['dictionary'];
        $parent = $dictionaries[$field['dictionary']]->rawValue('parent');
        if($parent > 0 && (!empty($field['parent']) || !empty($field['parent2']))) {
            $parentConds = array();
            if(!empty($field['parent'])) {
                if (is_array($field['parent'])) {

                } else {
                    $parentConds[] = "parent=" . cot::$db->quote($field['parent']);
                }
            }

            if(!empty($field['parent2'])) {
                if (is_array($field['parent2'])) {

                } else {
                    $parentConds[] = "parent2=" . cot::$db->quote($field['parent2']);
                }
            }

            $orWhere[] = "(dictionary={$field['dictionary']} AND ".implode(' AND ', $parentConds).")";
        } else {
            if(!empty($field['condition'])) {
                $tmp = "(dictionary={$field['dictionary']} AND ".$field['condition'].")";
                $orWhere[] = $tmp;
                unset($rootIds[$field['dictionary']]);
            }

        }
    }
    reset($config);

    //$condition = array(array('dictionary', $dicIds));
    $condition = array();
    if(!empty($rootIds)) $orWhere[] = 'dictionary IN ('.implode(', ', $rootIds).')';

    if(!empty($orWhere)) {
        $condition[] = array('SQL', implode(' OR ', $orWhere));
    }

    // Значения упорядоченные по словарям
    $values = array();

    if(!empty($condition)) {
        $vals = dictionary_model_Value::find($condition, 0, 0, array(array('value', 'ASC')));
        if($vals) {
            foreach($vals as $val) {
                $values[$val->rawValue('dictionary')][$val->id] = $val->value;
            }
        }
    }

    $dic_hasChilds = false;
    if(!empty($values)) {
        foreach($config as $field) {
            $extFld = &$cot_extrafields[$field['location']][$field['field']];

            // Включаем и пустое значение для select'a
            $variants = $extFld['field_variants'];
            $type = $extFld['field_type'];

            if (in_array($type, array('select'))){
                cot::$L[$extFld['field_name'].'_0'] = cot::$R['code_option_empty'];
                if(!empty($variants)) {
                    $variants = '0,'.$variants;
                } else {
                    $variants = '0';
                }
            }
            $extFld['field_default'] = '0';

            if(!empty($values[$field['dictionary']])) {
                if ($variants != '') $variants .= ',';
                $variants .= implode(',', array_keys($values[$field['dictionary']]));

                // Заполняем текстовое представление
                foreach($values[$field['dictionary']] as $key => $val) {
                    if(empty(cot::$L[$extFld['field_name'].'_'.$key])) {
                        cot::$L[$extFld['field_name'].'_'.$key] = $val;
                    }
                }
                //var_dump_($values[$field['dictionary']]);

            }
            $extFld['field_variants'] = $variants;

            // Аттрибуты для элементов управления
            $tmpAttrs = array();
            if(isset($dictionaries[$field['dictionary']])) {
                $tmpAttrs['dic_id'] = $field['dictionary'];
                if($extFld['field_type'] == 'select') $tmpAttrs['class'] = "form-control";

                $tmpAttrs['id'] = "dic_{$field['dictionary']}";
                if($dictionaries[$field['dictionary']]->hasChilds) {
                    $dic_hasChilds = true;
                    $tmpAttrs['class']="has_childs form-control";
                    $tmpAttrs['all_child_vals'] = 1;  // При редактировании страницы выводить полный список потомков
                }
                if ($dictionaries[$field['dictionary']]->rawValue('parent') > 0){
                    $tmpAttrs['parent_dic']= $dictionaries[$field['dictionary']]->rawValue('parent');
                    // Пока зависимость только от первого родителя
                    $tmpAttrs['parent_el'] = 'dic_'.$dictionaries[$field['dictionary']]->rawValue('parent');
                }
                if ($dictionaries[$field['dictionary']]->rawValue('parent2') > 0){
                    //$tmpAttrs['parent2_dic']= $dictionaries[$field['dictionary']]->rawValue('parent2');
                }

                if(!empty($field['parent'])) {
                    $tmpAttrs['parent_val'] = $field['parent'];
                }

                if(!empty($field['parent2'])) {
                    $tmpAttrs['parent2_val'] = $field['parent2'];
                }
            }

            if (count($tmpAttrs) > 0){
                $rc_name = preg_match('#^(\w+)\[(.*?)\]$#', $extFld['field_name'], $mt) ? $mt[1] : $extFld['field_name'];
                $rc = empty(cot::$R["input_select_{$rc_name}"]) ? (empty($field['rc']) ? 'input_select' : $field['rc']) : "input_select_{$rc_name}";

                switch($extFld['field_type']) {
                    case 'radio':
                        $rc = empty(cot::$R["input_radio_{$rc_name}"]) ? (empty($field['rc']) ? 'input_radio' : $field['rc']) : "input_radio_{$rc_name}";
                        break;
                }
                $tmpHtml =  str_replace('{$attrs}', cot_rc_attr_string($tmpAttrs), cot::$R[$rc]);
                $extFld['field_html'] = $tmpHtml;
            }
        }
    }

    if ($dic_hasChilds) Resources::linkFileFooter(cot::$cfg['plugins_dir']."/dictionary/js/dictionary.js");
}