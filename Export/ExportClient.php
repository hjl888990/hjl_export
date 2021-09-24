<?php
/**
 * Created by PhpStorm.
 * User: 11956
 * Date: 2020/6/1
 * Time: 15:19
 */

namespace Export;

use Export\Storage\MultiCsv;
use Export\Storage\MultiPhpofficeExcel;
use Export\Storage\MultiXlsWriteExcel;

class ExportClient
{
    //导出文件类型
    const EXPORT_STORAGE_TYPE_OF_CSV = 1;//csv

    const EXPORT_STORAGE_TYPE_OF_XLSWRITE_EXCEL = 2;//excel xlswrite插件

    const EXPORT_STORAGE_TYPE_OF_PHPOFFICE_EXCEL = 3;//excel phpoffice插件


    const DATA_TYPE_OF_STRING = 1;

    const DATA_TYPE_OF_NUMERIC = 2;

    const DATA_TYPE_OF_LINK = 3;

    const DATA_TYPE_OF_STRING_BG_COLOR = 4;

    const DATA_TYPE_OF_WEB_URL = 5;

    /**
     * 获取存储模式
     * @param $store_type 导出文件类型
     * @param $export_tmp_path 导出文件临时目录
     * @return MultiCsv|MultiPhpofficeExcel|MultiXlsWriteExcel
     * @throws \Exception
     */
    public function getExportClient($store_type, $export_tmp_path) {
        if (empty($store_type) || empty($export_tmp_path)) {
            throw new \Exception('getExportClient params is empty');
        }
        switch ($store_type) {
            case self::EXPORT_STORAGE_TYPE_OF_CSV:
                $storage_client = new MultiCsv($export_tmp_path);
                break;
            case self::EXPORT_STORAGE_TYPE_OF_XLSWRITE_EXCEL:
                $storage_client = new MultiXlsWriteExcel($export_tmp_path);
                break;
            case self::EXPORT_STORAGE_TYPE_OF_PHPOFFICE_EXCEL:
                $storage_client = new MultiPhpofficeExcel($export_tmp_path);
                break;
            default:
                throw new \Exception('getExportClient params[store_type] error');
        }
        return $storage_client;
    }

}