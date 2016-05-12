<?php
defined('COT_CODE') or die('Wrong URL.');

/**
 * Model class for the Dictionary
 *
 * @package Dictionary
 * @author Kalnov Alexey    <kalnovalexey@yandex.ru>
 * @copyright © Portal30 Studio http://portal30.ru
 *
 * @method static dictionary_model_Dictionary getById($pk);
 * @method static dictionary_model_Dictionary fetchOne($conditions = array(), $order = '');
 * @method static dictionary_model_Dictionary[] find($conditions = array(), $limit = 0, $offset = 0, $order = '');
 *
 * @property int $id
 * @property string $title
 * @property dictionary_model_Dictionary $parent
 * @property dictionary_model_Dictionary $parent2
 *
 * @property int $valueCount   Количество значений
 */
class dictionary_model_Dictionary extends Som_Model_ActiveRecord
{
    /** @var Som_Model_Mapper_Abstract $db */
    protected static $_db = null;
    protected static $_tbname = '';
    protected static $_primary_key = 'id';

    protected $_valuesCount = null;

    /**
     * Static constructor
     * @param string $db Data base connection config name
     */
    public static function __init($db = 'db')
    {
        static::$_tbname = cot::$db->dictionary;
        parent::__init($db);
    }

    /**
     * Retrieve a key => val list from the database.
     *
     * @param array $conditions
     * @param int $limit
     * @param int $offset
     * @param string $order
     * @param string $field
     * @return array
     */
    public static function keyValPairs($conditions = array(), $limit = 0, $offset = 0, $order = '', $field = 'title') 
    {
        if(empty($order)) $order = array(array('title', 'ASC'));
        return parent::keyValPairs($conditions, $limit, $offset, $order, $field);
    }

    public function getValueCount()
    {
        if(is_null($this->_valuesCount)) {
            $this->_valuesCount = dictionary_model_Value::count(array(array('dictionary', $this->_data['id'])));
        }

        return $this->_valuesCount;
    }

    public function beforeDelete()
    {
        // Удалить все значения
        $values = dictionary_model_Value::find(array(array('dictionary', $this->_data['id'])));
        if($values) {
            foreach($values as $key => $valueRow) {
                $valueRow->delete();
                unset($values[$key]);
            }
        }

        // Зачистить родительсктие отношения
        cot::$db->update(cot::$db->dictionary, array('parent'=>0), 'parent='.$this->_data['id']);
        cot::$db->update(cot::$db->dictionary, array('parent2'=>0), 'parent2='.$this->_data['id']);

        return parent::beforeDelete();
    }

    public static function fieldList()
    {
        return array(
            'id' =>
                array(
                    'type' => 'int',
                    'primary' => true,
                ),
            'parent' =>
                array(
                    'type' => 'link',
                    'default' => 0,
                    'link' =>
                        array(
                            'model' => 'dictionary_model_Dictionary',
                            'relation' => 'toonenull',
                            'label' => 'title',
                        ),
                ),
            'parent2' =>
                array(
                    'type' => 'link',
                    'default' => 0,
                    'link' =>
                        array(
                            'model' => 'dictionary_model_Dictionary',
                            'relation' => 'toonenull',
                            'label' => 'title',
                        ),
                ),
            'title' =>
                array(
                    'type' => 'varchar',
                    'length' => 255,
                    'nullable' => false,
                ),
        );
    }

    // === Методы для работы с шаблонами ===
    /**
     * Returns all Group tags for coTemplate
     *
     * @param dictionary_model_Dictionary|int $item dictionary_model_Dictionary object or ID
     * @param string $tagPrefix Prefix for tags
     * @param bool $cacheitem Cache tags
     * @return array|void
     *
     */
    public static function generateTags($item, $tagPrefix = '', $cacheitem = true){

        static $extp_first = null, $extp_main = null;
        static $cacheArr = array();

        if (is_null($extp_first)){
            $extp_first = cot_getextplugins('dictionary.tags.first');
            $extp_main  = cot_getextplugins('dictionary.tags.main');
        }

        /* === Hook === */
        foreach ($extp_first as $pl){
            include $pl;
        }
        /* ===== */

        list($usr['auth_read'], $usr['auth_write'], $usr['isadmin']) = cot_auth('files', 'a');

        if ( ($item instanceof dictionary_model_Dictionary) && is_array($cacheArr[$item->id]) ) {
            $temp_array = $cacheArr[$item->file_id];
        }elseif (is_int($item) && is_array($cacheArr[$item])){
            $temp_array = $cacheArr[$item];
        }else{
            if (is_int($item) && $item > 0){
                $item = files_model_File::getById($item);
            }

            /** @var dictionary_model_Dictionary $item  */
            if ($item && $item->id > 0){

                $date_format = 'datetime_medium';
                $temp_array = array(
                    'ID' => $item->id,
                    'URL' => cot_url('admin', array('m' => 'other', 'p'=>'dictionary', 'a' => 'values', 'did' => $item->id)),
                    'DELETE_URL' => cot_confirm_url(cot_url('admin', array('m'=>'other', 'p'=>'dictionary',
                        'a'=>'delete', 'did'=>$item->id), '', true) ),
                    'TITLE' => htmlspecialchars($item->title),
                    'PARENT_ID' => (!empty($item->parent)) ? $item->parent->id : 0,
                    'PARENT_TITLE' => (!empty($item->parent)) ? htmlspecialchars($item->parent->title) : "",
                    'PARENT2_ID' => (!empty($item->parent2)) ? $item->parent2->id : 0,
                    'PARENT2_TITLE' => (!empty($item->parent2)) ? htmlspecialchars($item->parent2->title) : "",
                    'VALUE_COUNT' => $item->valueCount,

//                    'UPDATED' => $item->file_updated,
//                    'UPDATE_DATE' => cot_date($date_format, strtotime($item->file_updated)),
//                    'UPDATED_RAW' => strtotime($item->file_updated),
                );

                /* === Hook === */
                foreach ($extp_main as $pl)
                {
                    include $pl;
                }
                /* ===== */
                $cacheitem && $cacheArr[$item->id] = $temp_array;
            }else{

            }
        }

        $return_array = array();
        foreach ($temp_array as $key => $val){
            $return_array[$tagPrefix . $key] = $val;
        }

        return $return_array;
    }

}

// Class initialization for some static variables
dictionary_model_Dictionary::__init();
