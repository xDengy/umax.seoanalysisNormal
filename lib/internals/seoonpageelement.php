<?php
namespace Umax\Lib\Internals;

use Bitrix\Main\Localization\Loc;
Loc::loadMessages(__FILE__);

class UmaxSeoOnPageElementTable extends \UmaxAnalysisDataManager
{
    /**
     * Returns DB table name for entity.
     *
     * @return string
     */
    public static function getTableName()
    {
        return 'b_umax_seo_on_page_element';
    }

    /**
     * Returns entity map definition.
     *
     * @return array
     */
    public static function getMap()
    {
        return array(
            'ID' => array(
                'data_type' => 'integer',
                'primary' => true,
                'autocomplete' => true,
                'title' => 'ID',
            ),
            'page_url' => array(
                'data_type' => 'text',
                'required' => true,
                'title' => 'Страница',
            ),
            'IBLOCK_TYPE' => array(
                'data_type' => 'text',
                'required' => true,
                'title' => 'Тип страницы',
            ),
            'IBLOCK_ID' => array(
                'data_type' => 'text',
                'required' => true,
                'title' => 'Информационный блок',
            ),
            'ELEMENT_ID' => array(
                'data_type' => 'integer',
                'required' => true,
                'title' => 'ID элемента',
            ),
            'VALUE' => array(
                'data_type' => 'integer',
                'required' => true,
                'title' => 'Значение',
            ),
            'FULL_VALUE' => array(
                'data_type' => 'integer',
                'required' => true,
                'title' => 'Seo OnPage',
            ),
            'RED' => array(
                'data_type' => 'integer',
                'required' => true,
                'title' => 'Красных ошибок',
            ),
            'YELLOW' => array(
                'data_type' => 'integer',
                'required' => true,
                'title' => 'Желтых ошибок',
            ),
            'GREEN' => array(
                'data_type' => 'integer',
                'required' => true,
                'title' => 'Без ошибок',
            ),
            'BLUE' => array(
                'data_type' => 'integer',
                'required' => true,
                'title' => 'Синих ошибок',
            ),
            'DATE_CHANGE' => array(
                'data_type' => 'text',
                'required' => true,
                'title' => 'Дата изменения',
            ),
        );
    }
}
