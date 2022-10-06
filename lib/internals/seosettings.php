<?php
namespace Umax\Lib\Internals;

use Bitrix\Main\Localization\Loc;
Loc::loadMessages(__FILE__);

class UmaxSeoSettingsTable extends \UmaxAnalysisDataManager
{
    /**
     * Returns DB table name for entity.
     *
     * @return string
     */
    public static function getTableName()
    {
        return 'b_umax_seo_settings';
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
            'GOODS' => array(
                'data_type' => 'integer',
                'required' => true,
                'title' => 'GOODS',
            ),
            'SERVICE' => array(
                'data_type' => 'integer',
                'required' => true,
                'title' => 'SERVICE',
            ),
            'NEWS' => array(
                'data_type' => 'integer',
                'required' => true,
                'title' => 'NEWS',
            ),
        );
    }
}
