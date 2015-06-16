<?php
(defined('COT_CODE') && defined('COT_ADMIN')) or die('Wrong URL.');

/**
 * Dictionary plugin for Cotonti
 *
 * @package Dictionary
 * @author Alex - Studio Portal30
 * @copyright Portal30 2014 http://portal30.ru
 */
class AdminMainController{

    /**
     * Main (index) Action.
     */
    public function indexAction(){
        global $adminpath, $admintitle;

        $adminpath[] = array(cot_url('admin', 'm=other&p=dictionary'), cot::$L['dict_dictionaries'] );
        $admintitle = cot::$L['dict_dictionaries'];

        $parentsArr = dictionary_model_Dictionary::keyValPairs(array(array('parent', 0)));

        $act = cot_import('act', 'P', 'TXT');
        $dict = new dictionary_model_Dictionary();
        $errors = 0;

        if($act == 'save'){
            $did = cot_import('did', 'P', 'INT');
            if($did > 0){
                $dict = dictionary_model_Dictionary::getById($did);
                if(!$dict) cot_die(true, true);
            }

            $_POST['parent'] = (int)cot_import('parent', 'P', 'INT');
            $_POST['parent2'] = (int)cot_import('parent2', 'P', 'INT');

            $dict->setData($_POST);
            cot_check($dict->title == '', 'Заголовок обязателен к заполнению', 'title');

            if(!cot_error_found()){
                $dict->save();
                cot_message(cot::$L['Saved']);
                cot_redirect(cot_url('admin', array('m'=>'other', 'p'=>'dictionary')));
            } else {
                $errors = 1;
            }

        }

        $dictionaries = dictionary_model_Dictionary::find(array(), 0, 0, array(array('title', 'ASC')));

        $tpl = new XTemplate(cot_tplfile('dictionary.admin.main', 'plug'));

        $tpl->assign(array(
//            'PAGE_TITLE' => cot::$L['dict_dictionaries'],
            'IS_ERROR' => $errors,
            'TOTAL_ITEMS' => (!empty($dictionaries)) ? count($dictionaries) : 0,
        ));

        if(!empty($dictionaries)){
            $i = 0;
            foreach($dictionaries as $dictionaryRow) {
                $i++;
                $tpl->assign($dictionaryRow::generateTags($dictionaryRow, 'DICT_ROW_'));
                $tpl->assign(array(
                    'DICT_ROW_NUM' => $i,
                ));
                $tpl->parse('MAIN.DICTIONARY_ROW');
            }
        }

        $tpl->assign(array(
            'NEW_FORM_URL'  => cot_url('admin', array('m'=>'other', 'p'=>'dictionary'), '', true),
            'NEW_PARENT'    => cot_selectbox($dict->parent, "parent",  array_keys($parentsArr), array_values($parentsArr),
                true, array('class' => 'form-control', 'id' => 'editDictDialog-parent')),
            'NEW_PARENT2'   => cot_selectbox($dict->parent2, "parent2",  array_keys($parentsArr), array_values($parentsArr),
                true, array('class' => 'form-control', 'id' => 'editDictDialog-parent2')),
            'NEW_TITLE'     => cot_inputbox('text', 'title',  $dict->title,
                array('class' => 'form-control', 'id' => 'editDictDialog-title')),
        ));
        $tpl->parse('MAIN.ADDFORM');

        // Error and message handling
        cot_display_messages($tpl);

        $tpl->parse('MAIN');
        return $tpl->text();
    }


    /**
     * Удаление Словаря
     */
    public function deleteAction() {
        $did = cot_import('did', 'G', 'INT');
        if(!$did) {
            cot_error(cot::$L['dict_not_found']);
        }

        $dictionary = dictionary_model_Dictionary::getById($did);
        if(!$dictionary) {
            cot_error(cot::$L['dict_not_found']);
        }
        $title = $dictionary->title;

        $dictionary->delete();

        cot_message(cot::$L['dict_deleted'].": «{$title}»");
        cot_redirect(cot_url('admin', array('m'=>'other', 'p'=>'dictionary'), '', true));
    }


    public function ajxDictionaryInfoAction(){

        $ret = array('error' => '');

        $did = cot_import('did', 'G', 'INT');
        if(!$did) $did = cot_import('did', 'P', 'INT');
        if(!$did) {
            $ret['error'] = cot::$L['dict_not_found'];
            echo json_encode($ret);
            exit;
        }

        $dictionary = dictionary_model_Dictionary::getById($did);
        if(!$dictionary) {
            $ret['error'] = cot::$L['dict_not_found'];
            echo json_encode($ret);
            exit;
        }

        $ret['dict'] = $dictionary->toArray();

        echo json_encode($ret);
        exit;
    }

    public function valuesAction(){
        global $adminpath, $admintitle;


        if(Resources::getAlias('select2')) {
            Resources::linkFileFooter(Resources::getAlias('select2'));
        } else {
            Resources::linkFile('lib/select2/css/select2.min.css');
            Resources::linkFileFooter('lib/select2/js/select2.min.js');
            Resources::linkFileFooter('lib/select2/js/i18n/ru.js');
        }
        Resources::embedFooter('$("select.select2").select2();
                ajaxSuccessHandlers.push(function(){
                    $("select.select2").select2();
                });');

        $did = cot_import('did', 'G', 'INT');
        if(!$did) {
            cot_error(cot::$L['dict_not_found']);
            cot_redirect(cot_url('admin', array('m'=>'other', 'p'=>'dictionary'), '', true));
        }

        $dictionary = dictionary_model_Dictionary::getById($did);
        if(!$dictionary) {
            cot_error(cot::$L['dict_not_found']);
            cot_redirect(cot_url('admin', array('m'=>'other', 'p'=>'dictionary'), '', true));
        }

        // Пагинация
        $maxrowsperpage = cot::$cfg['maxrowsperpage'];
        list($pg, $d, $durl) = cot_import_pagenav('d', $maxrowsperpage); //page number for pages list
        if($pg > 1) cot::$out['subtitle'] .= cot_rc('code_title_page_num', array('num' => $pg));

        $urlParams = array('m'=>'other', 'p'=>'dictionary', 'a'=>'values', 'did'=>$did);

        $title = $admintitle = cot::$L['dict_dictionary'].' «'.$dictionary->title.'»';
        $adminpath[] = array(cot_url('admin', 'm=other&p=dictionary'), cot::$L['dict_dictionaries'] );
        $adminpath[] = array(cot_url('admin', $urlParams), $title);

        $condition = array(
            array('dictionary', $did),
        );

        // Фильтры
        $f = array(
            'value' => trim(cot_import('f_value', 'G', 'TXT')),
            'parent' => cot_import('f_parent', 'G', 'INT'),
            'parent2' => cot_import('f_parent2', 'G', 'INT'),
        );

        if($f['value'] != '') {
            $condition[] = array('value', "*{$f['value']}*");
            $urlParams['f_value'] = $f['value'];
        }
        if($f['parent'] > 0) {
            $condition[] = array('parent', $f['parent']);
            $urlParams['f_parent'] = $f['parent'];
        }
        if($f['parent2'] > 0) {
            $condition[] = array('parent2', $f['parent2']);
            $urlParams['f_parent2'] = $f['parent2'];
        }
        // /Фильтры

        $totallines = dictionary_model_Value::count($condition);

        $totalpages = ceil($totallines / $maxrowsperpage);
        if($totalpages == 0) $totalpages = 1;
        if($pg > 1 && $totalpages < $pg){
            if($totalpages > 1) $urlParams['d'] = $totalpages;
            cot_redirect(cot_url('admin', $urlParams, '', true));
        }

        $tpl = cot_tplfile(array('dictionary', 'admin', 'values'), 'plug');
        $t = new XTemplate($tpl);

        $values = null;

        $t->assign(dictionary_model_Dictionary::generateTags($dictionary, 'DICT_'));

        $tmp = $urlParams;
        if($pg > 1) $tmp['d'] = $pg;
        $hidden = cot_inputbox('hidden', 'ret_url', cot_url('admin', $tmp, '', true));

        $parentArr = null;
        if(!empty($dictionary->parent)){
            $parentArr = dictionary_model_Value::keyValPairs(array(array('dictionary', $dictionary->parent->id)));
        }

        $parent2Arr = null;
        if(!empty($dictionary->parent2)){
            $parent2Arr = dictionary_model_Value::keyValPairs(array(array('dictionary', $dictionary->parent2->id)));
        }


        /* === New Value Form === */
        $hidden .= cot_inputbox('hidden', 'did', $did);
        $formParams = array('m'=>'other', 'p'=>'dictionary', 'a'=>'valueNew');
        $t->assign(array(
            'NEW_FORM_URL' => cot_url('admin', $formParams, '', true),
            'NEW_HIDDEN'   => $hidden,
            'NEW_PARENT'   => '',
            'PARENT_TITLE' => '',
            'NEW_PARENT2'  => '',
            'PARENT2_TITLE' => '',
            'NEW_VALUE'     => cot_inputbox('text', 'value',  ''),
        ));

        if(!empty($parentArr)){
            $t->assign(array(
                'NEW_PARENT' => cot_selectbox($f['parent'], "parent",  array_keys($parentArr), array_values($parentArr), true,
                    array('class'=>'form-control select2')),
                'PARENT_TITLE' => htmlspecialchars($dictionary->parent->title),
            ));
        }
        if(!empty($parent2Arr)){
            $t->assign(array(
                'NEW_PARENT2' => cot_selectbox($f['parent2'], "parent2",  array_keys($parent2Arr), array_values($parent2Arr), true,
                    array('class'=>'form-control select2')),
                'PARENT2_TITLE' => htmlspecialchars($dictionary->parent2->title),
            ));
        }
        $t->parse('MAIN.ADDFORM');
        /* === /New Value Form === */


        if($totallines > 0){
            $values = dictionary_model_Value::find($condition, $maxrowsperpage, $d, array(
                array('value', 'asc')
            ));

            $tmp = $urlParams;
            if($pg > 1) $tmp['d'] = $pg;
            $retUrl = cot_url('admin', $tmp, '', true);

            $jj = $d;
            $row_counter = 0;
            foreach($values as $valueRow) {
                $row_counter++;
                $jj++;
                $t->assign(dictionary_model_Value::generateTags($valueRow, 'LIST_ROW_'));
                $t->assign(array(
                    'LIST_ROW_ODDEVEN'      => cot_build_oddeven($jj),
                    'LIST_ROW_EDIT_VALUE'   => cot_inputbox('text', "value[{$valueRow->id}]",  $valueRow->value),
                    'LIST_ROW_EDIT_PARENT'  => '',
                    'LIST_ROW_EDIT_PARENT2' => '',
                    'LIST_ROW_DELETE_URL'   => cot_confirm_url(cot_url('admin', array('m'=>'other', 'p'=>'dictionary',
                        'a'=>'valueDelete', 'id'=>$valueRow->id, 'ret_url' => base64_encode($retUrl))),
                        'dictionary', 'conf_delete_value' ),
                    'LIST_ROW_NUM' => $jj,
                ));
                if(!empty($parentArr)) {
                    $t->assign(array(
                        'LIST_ROW_EDIT_PARENT' =>  cot_selectbox($valueRow->parent->id, "parent[{$valueRow->id}]",
                            array_keys($parentArr), array_values($parentArr), true,
                            array('class'=>'form-control select2')),
                    ));
                }
                if(!empty($parent2Arr)) {
                    $t->assign(array(
                        'LIST_ROW_EDIT_PARENT2' =>  cot_selectbox($valueRow->parent2->id, "parent2[{$valueRow->id}]",
                            array_keys($parent2Arr), array_values($parent2Arr), true,
                            array('class'=>'form-control select2')),
                    ));
                }
                $t->parse('MAIN.LIST_ROW');
            }

            $tmp = $urlParams;
            if($pg > 1) $tmp['d'] = $pg;
            $hidden = cot_inputbox('hidden', 'ret_url', cot_url('admin', $tmp, '', true));
            $t->assign(array(
                'MASS_SAVE_URL' =>  cot_url('admin', array('m'=>'other', 'p'=>'dictionary', 'a'=>'valueMassSave')),
                'MASS_SAVE_HIDDEN' =>  $hidden,
            ));

        }

        /* === Filter Form === */
        $formParams = array('m'=>'other', 'p'=>'dictionary', 'a'=>'values', 'did'=>$did);
        $hidden = '';
        foreach($formParams as $key => $val){
            $hidden .= cot_inputbox('hidden', $key, $val);
        }
        $t->assign(array(
            'FILTER_FORM_URL' => cot_url('admin', $formParams, '', true),
            'FILTER_HIDDEN'   => $hidden,
            'FILTER_PARENT'   => '',
            'FILTER_PARENT2'  => '',
            'FILTER_VALUE'     => cot_inputbox('text', 'f_value',  $f['value']),
        ));
        if(!empty($parentArr)){
            $t->assign(array(
                'FILTER_PARENT' => cot_selectbox($f['parent'], "f_parent",  array_keys($parentArr), array_values($parentArr), true,
                    array('class'=>'form-control select2')),
            ));
        }
        if(!empty($parent2Arr)){
            $t->assign(array(
                'FILTER_PARENT2' => cot_selectbox($f['parent2'], "f_parent2",  array_keys($parent2Arr), array_values($parent2Arr), true,
                    array('class'=>'form-control select2')),
            ));
        }
        $t->parse('MAIN.FILTER');
        /* === /Filter Form === */

//        $pagenav = cot_pagenav('admin', $urlParams, $d, $totallines, $maxrowsperpage);
        $pagenav = cot_pagenav('admin', $urlParams, $d, $totallines, $maxrowsperpage, 'd', '', cot::$cfg['jquery'] && cot::$cfg['turnajax']);

        if($totallines == 0) $t->parse('MAIN.EMPTY');

        $t->assign(array(
            'PAGE_TITLE'  =>  $title,

            'PAGINATION' => $pagenav['main'],
            'PAGEPREV' => $pagenav['prev'],
            'PAGENEXT' => $pagenav['next'],
            'CURRENTPAGE' => $pagenav['current'],
            'TOTALITEMS' => $totallines,
            'MAXPERPAGE' => $maxrowsperpage,
            'TOTALPAGES' => $pagenav['total'],
            'ON_THIS_PAGE' => $row_counter
        ));

        // Error and message handling
        cot_display_messages($t);

        $t->parse();
        return  $t->text();
    }


    public function valueNewAction(){

        $did = cot_import('did', 'P', 'INT');
        $retUrl = cot_import('ret_url', 'P', 'TXT');

        if(!$did) {
            cot_error(cot::$L['dict_not_found']);
            cot_redirect($retUrl);
        }

        $dictionary = dictionary_model_Dictionary::getById($did);
        if(!$dictionary) {
            cot_error(cot::$L['dict_not_found']);
            cot_redirect($retUrl);
        }

//        $data = $_POST;
//        unset($data['x']);
//        unset($data['ret_url']);
//        unset($data['did']);
        $data = array();
        $data['value']   = trim(cot_import('value', 'P', 'TXT'));
        cot_check(empty($data['value']), cot::$L['dict_value_empty'], 'value');
        $data['parent']  = intval(cot_import('parent', 'P', 'INT'));
        $data['parent2'] = intval(cot_import('parent2', 'P', 'INT'));
        $data['dictionary'] = $did;


        if(!cot_error_found()){
            $dictionary = new dictionary_model_Value();
            $dictionary->setData($data);
            $dictionary->save();
            cot_message(cot::$L['Saved']);
        }

        cot_redirect($retUrl);
    }

    /**
     * Массовое сохранение значений
     */
    public function valueMassSaveAction() {
        global $Ls;

        $retUrl = cot_import('ret_url', 'P', 'TXT');

        $valueArr   = cot_import('value', 'P', 'ARR');
        $parentArr  = cot_import('parent', 'P', 'ARR');
        $parent2Arr = cot_import('parent2', 'P', 'ARR');
        $saved = 0;

        foreach($valueArr as $id => $value){
            $id = (int)$id;
            if(!$id) continue;

            $data = array(
                'value'   => trim(cot_import($valueArr[$id], 'D', 'TXT')),
                'parent'  => intval(cot_import($parentArr[$id], 'D', 'INT')),
                'parent2' => intval(cot_import($parent2Arr[$id], 'D', 'INT')),
            );

            if($data['value'] == '') continue;

            $saved += cot::$db->update(cot::$db->dictionary_values, $data, "id={$id}");
        }
        if ($saved) cot_message(cot::$L['Saved'].' '.cot_declension($saved, $Ls['dict_value']));

        cot_redirect($retUrl);
    }

    /**
     * Удалить значение в словаре
     */
    public function valueDeleteAction(){

        $retUrl = cot_import('ret_url', 'G', 'TXT');
        $retUrl = base64_decode($retUrl);

        $id = cot_import('id', 'G', 'INT');
        if (!$id){
            cot_error(cot::$L['dict_value_not_found']);
            cot_redirect($retUrl);
        }

        // Получить значение, чтобы плагины могли проанализировать его перед удалением
        $value = dictionary_model_Value::getById($id);
        if (!$value){
            cot_error(cot::$L['dict_value_not_found']);
            cot_redirect($retUrl);
        }

        $title = $value->value;

        // Удалить значение
        $value->delete();

        cot_message(cot::$L['dict_value_deleted'].": «{$title}»");
        cot_redirect($retUrl);
    }
}
