<?php
namespace Umax\Lib\Internals;

use Bitrix\Main\Localization\Loc;
Loc::loadMessages(__FILE__);

class UmaxSeoOnPageElementErrorsTable extends \UmaxAnalysisDataManager
{
    /**
     * Returns DB table name for entity.
     *
     * @return string
     */
    public static function getTableName()
    {
        return 'b_umax_seo_on_page_element_errors';
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
            'IBLOCK_TYPE' => array(
                'data_type' => 'text',
                'required' => true,
                'title' => 'IBLOCK_TYPE',
            ),
            'ELEMENT_ID' => array(
                'data_type' => 'integer',
                'required' => true,
                'title' => 'ELEMENT_ID',
            ),
            'ERROR_ID' => array(
                'data_type' => 'integer',
                'required' => true,
                'title' => 'ERROR_ID',
            ),
        );
    }
}
