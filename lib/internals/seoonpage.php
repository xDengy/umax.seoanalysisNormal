<?php
namespace Umax\Lib\Internals;

use Bitrix\Main\Localization\Loc;
Loc::loadMessages(__FILE__);

class UmaxSeoOnPageTable extends \UmaxAnalysisDataManager
{
    /**
     * Returns DB table name for entity.
     *
     * @return string
     */
    public static function getTableName()
    {
        return 'b_umax_seo_on_page';
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
            'type' => array(
                'data_type' => 'text',
                'required' => false,
                'title' => 'Тип',
            ),
            'seo_on_page' => array(
                'data_type' => 'integer',
                'required' => false,
                'title' => 'Seo OnPage',
            ),
        );
    }
}
