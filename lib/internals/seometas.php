<?php
namespace Umax\Lib\Internals;

use Bitrix\Main\Localization\Loc;
use Umax\Lib\Internals\UmaxSeoAnalysisTable;
Loc::loadMessages(__FILE__);

class UmaxMetasTable extends \UmaxAnalysisDataManager
{
    /**
     * Returns DB table name for entity.
     *
     * @return string
     */
    public static function getTableName()
    {
        return 'b_umax_metas';
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
            'title' => array(
                'data_type' => 'text',
                'required' => false,
                'title' => 'Title',
            ),
            'description' => array(
                'data_type' => 'text',
                'required' => false,
                'title' => 'Description',
            ),
            'h1' => array(
                'data_type' => 'text',
                'required' => false,
                'title' => 'H1',
            ),
            'title_dubles' => array(
                'data_type' => 'text',
                'required' => false,
                'title' => 'Дубли title',
            ),
            'description_dubles' => array(
                'data_type' => 'text',
                'required' => false,
                'title' => 'Дубли description',
            ),
            'h1_dubles' => array(
                'data_type' => 'text',
                'required' => false,
                'title' => 'Дубли h1',
            ),
        );
    }

    public static function ude() {
        $raw = array();
        $args = self::getList(['select' => ['ID', 'page_url', 'title', 'description', 'h1']])->fetchAll();
        $i = 1;
        $meta = [];
        foreach($args as $arg) {
            if(is_array($arg)) {
                foreach($arg as $key => $value) {
                    if($key !== 'page_url' && $key !== 'ID') {
                        if(!$value)
                            $value = 'empty';
                        $raw[$key][$value][] = $value;
                        $meta[$key][$value][] = ['ID' => $arg['ID'], 'page_url' => $arg['page_url'], 'value' => $value];
                    }
                }
                $i++;
            }
        }

        $out = [];
        $outMeta = [];
        foreach($raw as $key => $value) {
            $subKey = $key;
            if($subKey == 'description')
                $subKey = 'desc';
            $subKey = mb_strtoupper($subKey);
            $out = array_merge(array(
                'META_'.$subKey.'_UNIQUE' => 0,
                'META_'.$subKey.'_NOUNIQUE' => 0,
                'META_'.$subKey.'_EMPTY' => 0,
            ), $out);
            foreach ($value as $k => $v) {
                if(count($v) > 1) {
                    if($k !== 'empty') {
                        $out['META_'.$subKey.'_NOUNIQUE'] += count($v);
                        foreach ($meta[$key][$k] as $doubleKey => $double) {
                            $outMeta[$key][$double['ID']] = ['page_url' => $double['page_url'], 'value' => $v];
                        }
                    }
                    else
                        $out['META_'.$subKey.'_EMPTY'] += count($v);
                } else
                    $out['META_'.$subKey.'_UNIQUE'] += 1;
            }
        }
        $newMeta = [];
        foreach ($outMeta as $key => $value) {
            foreach ($value as $k => $v) {
                foreach ($value as $secKey => $secValue) {
                    if($v['value'] == $secValue['value'] && $v['page_url'] !== $secValue['page_url'])
                        $newMeta[$k][$key . '_dubles'][] = $secValue['page_url'];
                }
            }
        }
        foreach ($newMeta as $key => $value) {
            foreach ($value as $k => $v) {
                $newMeta[$key][$k] = json_encode($v);
            }
        }

        $metaRes = self::updateMultiple($newMeta);
        
        $res = UmaxSeoAnalysisTable::update(1, $out);
        return [$res->isSuccess(), $metaRes];
    }
}
