<?php
namespace Umax\Lib\Internals;

use Bitrix\Main\Localization\Loc;
Loc::loadMessages(__FILE__);

class UmaxSeoAnalysisTable extends \UmaxAnalysisDataManager
{
    /**
     * Returns DB table name for entity.
     *
     * @return string
     */
    public static function getTableName()
    {
        return 'b_umax_analysis';
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
            'SUMMARY_SEO_ON_PAGE' => array(
                'data_type' => 'integer',
                'required' => true,
                'title' => 'Суммарно Seo OnPage',
            ),
            'SUMMARY_COMMERCE' => array(
                'data_type' => 'integer',
                'required' => true,
                'title' => 'Суммарно коммерции',
            ),
            'SUMMARY_INDEX' => array(
                'data_type' => 'integer',
                'required' => true,
                'title' => 'Суммарно страниц',
            ),
            'SUMMARY_META' => array(
                'data_type' => 'integer',
                'required' => true,
                'title' => 'Суммарно Мета-Тэгов',
            ),
            'SUMMARY_IMAGES' => array(
                'data_type' => 'integer',
                'required' => true,
                'title' => 'Суммарно изображений',
            ),
            'SEO_ON_PAGE_GOODS' => array(
                'data_type' => 'integer',
                'required' => true,
                'title' => 'Суммарно товаров',
            ),
            'SEO_ON_PAGE_SERVICE' => array(
                'data_type' => 'integer',
                'required' => true,
                'title' => 'Суммарно услуг',
            ),
            'SEO_ON_PAGE_NEWS' => array(
                'data_type' => 'integer',
                'required' => true,
                'title' => 'Суммарно статей',
            ),
            'PAGES_INDEX' => array(
                'data_type' => 'integer',
                'required' => true,
                'title' => 'Индексируется',
            ),
            'PAGES_NOINDEX' => array(
                'data_type' => 'integer',
                'required' => true,
                'title' => 'Не индексируется',
            ),
            'PAGES_ERROR' => array(
                'data_type' => 'integer',
                'required' => true,
                'title' => 'Ошибка',
            ),
            'META_TITLE_UNIQUE' => array(
                'data_type' => 'integer',
                'required' => true,
                'title' => 'Уникальный title',
            ),
            'META_TITLE_NOUNIQUE' => array(
                'data_type' => 'integer',
                'required' => true,
                'title' => 'Не уникальный title',
            ),
            'META_EMPTY' => array(
                'data_type' => 'integer',
                'required' => true,
                'title' => 'Пустой title',
            ),
            'META_DESC_UNIQUE' => array(
                'data_type' => 'integer',
                'required' => true,
                'title' => 'Уникальный description',
            ),
            'META_DESC_NOUNIQUE' => array(
                'data_type' => 'integer',
                'required' => true,
                'title' => 'Не уникальный description',
            ),
            'META_DESC_EMPTY' => array(
                'data_type' => 'integer',
                'required' => true,
                'title' => 'Пустой description',
            ),
            'META_H1_UNIQUE' => array(
                'data_type' => 'integer',
                'required' => true,
                'title' => 'Уникальный h1',
            ),
            'META_H1_NOUNIQUE' => array(
                'data_type' => 'integer',
                'required' => true,
                'title' => 'Не уникальный h1',
            ),
            'META_H1_EMPTY' => array(
                'data_type' => 'integer',
                'required' => true,
                'title' => 'Пустой h1',
            ),
            'INDEX_CLOSED' => array(
                'data_type' => 'integer',
                'required' => true,
                'title' => 'Индексация закрыта на странице',
            ),
            'INDEX_META_ROBOTS' => array(
                'data_type' => 'integer',
                'required' => true,
                'title' => 'Индексация закрыта в robots.txt',
            ),
            'INDEX_CANONICAL' => array(
                'data_type' => 'integer',
                'required' => true,
                'title' => 'Canonical',
            ),
            'INDEX_NOT_GET' => array(
                'data_type' => 'integer',
                'required' => true,
                'title' => 'Страница не 2ХХ',
            ),
            'IMAGES_ALL' => array(
                'data_type' => 'integer',
                'required' => true,
                'title' => 'Всего изображений на странице',
            ),
            'IMAGES_NO_ALT' => array(
                'data_type' => 'integer',
                'required' => true,
                'title' => 'Изображений без alt',
            ),
            'IMAGES_NO_TITLE' => array(
                'data_type' => 'integer',
                'required' => true,
                'title' => 'Изображений без title',
            ),
            'IMAGES_MAX_SIZE' => array(
                'data_type' => 'integer',
                'required' => true,
                'title' => 'Изображений с максимальным размером',
            ),
            'COMMERCE' => array(
                'data_type' => 'integer',
                'required' => true,
                'title' => 'Коммерция',
            ),
            'DATE_CHANGE' => array(
                'data_type' => 'text',
                'required' => true,
                'default' => date('Y-m-d H:m:s'),
                'title' => 'Дата изменения',
            ),
        );
    }
}
