<?php
namespace Umax\Lib\Internals;

use Bitrix\Main\Localization\Loc;
Loc::loadMessages(__FILE__);

class UmaxSummaryTable extends \UmaxAnalysisDataManager
{
    /**
     * Returns DB table name for entity.
     *
     * @return string
     */
    public static function getTableName()
    {
        return 'b_umax_summary';
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
                'title' => 'page_url',
            ),
            'responce' => array(
                'data_type' => 'text',
                'required' => true,
                'title' => 'responce',
            ),
            'seo_on_page' => array(
                'data_type' => 'integer',
                'required' => true,
                'title' => 'seo_on_page',
            ),
            'index' => array(
                'data_type' => 'boolean',
                'required' => true,
                'title' => 'index',
            ),
            'type' => array(
                'data_type' => 'text',
                'required' => false,
                'title' => 'type',
            ),
            'title' => array(
                'data_type' => 'text',
                'required' => false,
                'title' => 'title',
            ),
            'description' => array(
                'data_type' => 'text',
                'required' => false,
                'title' => 'description',
            ),
            'h1' => array(
                'data_type' => 'text',
                'required' => false,
                'title' => 'h1',
            ),
        );
    }
}
