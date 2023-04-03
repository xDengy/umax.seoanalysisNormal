<?php
namespace Umax\Lib\Internals;

use Bitrix\Main\Localization\Loc;
Loc::loadMessages(__FILE__);

class UmaxSeoErrorsTable extends \UmaxAnalysisDataManager
{
    /**
     * Returns DB table name for entity.
     *
     * @return string
     */
    public static function getTableName()
    {
        return 'b_umax_seo_errors';
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
            'KEY' => array(
                'data_type' => 'text',
                'required' => true,
                'title' => 'KEY',
            ),
            'NAME' => array(
                'data_type' => 'text',
                'required' => true,
                'title' => 'NAME',
            ),
            'DESC' => array(
                'data_type' => 'text',
                'required' => true,
                'title' => 'DESC',
            ),
            'RECOMENDS' => array(
                'data_type' => 'text',
                'required' => true,
                'title' => 'RECOMENDS',
            ),
            'VALUE' => array(
                'data_type' => 'integer',
                'required' => true,
                'title' => 'VALUE',
            ),
        );
    }
}
