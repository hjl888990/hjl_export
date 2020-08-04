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

    /**
     * 获取存储模式
     * @param $store_type
     * @param $export_file_path
     * @return MultiCsv|MultiPhpofficeExcel|MultiXlsWriteExcel
     * @throws \Exception
     */
    public function getExportStorage($store_type, $export_file_path) {
        switch ($store_type) {
            case self::EXPORT_STORAGE_TYPE_OF_CSV:
                $storage_class = new MultiCsv($export_file_path);
                break;
            case self::EXPORT_STORAGE_TYPE_OF_XLSWRITE_EXCEL:
                $storage_class = new MultiXlsWriteExcel($export_file_path);
                break;
            case self::EXPORT_STORAGE_TYPE_OF_PHPOFFICE_EXCEL:
                $storage_class = new MultiPhpofficeExcel($export_file_path);
                break;
            default:
                throw new \Exception('store_type is null');
        }
        return $storage_class;
    }
}