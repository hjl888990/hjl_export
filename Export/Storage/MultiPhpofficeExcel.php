<?php
/**
 * Created by PhpStorm.
 * User: hjl
 * Date: 2020/5/22
 * Time: 14:26
 */

namespace Export\Storage;

use Export\ExportClient;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use \PhpOffice\PhpSpreadsheet\Spreadsheet;
use \PhpOffice\PhpSpreadsheet\IOFactory;


class MultiPhpofficeExcel extends ExportStorageAbstract
{

    protected $handles_filename_mp = [];

    protected $handles_sheet_mp = [];

    protected $handles_sheet_column_datatype_mp = [];

    protected $handles_sheet_row_mp = [];


    public function __construct($export_tmp_path) {
        parent::__construct($export_tmp_path);
    }

    public function addHandle($key, $export_relative_path = '') {
        $export_file_path                = $this->export_basic_path . $export_relative_path;
        $spreadsheet                     = new Spreadsheet();
        $handle_sheet                    = $spreadsheet->getActiveSheet();
        $tmp_name                        = 'filename' . $key . ".xlsx";//临时文件名
        $this->handles[$key]             = $spreadsheet;
        $this->handles_sheet_mp[$key]    = $handle_sheet;
        $this->handles_filename_mp[$key] = $export_file_path . '/' . $tmp_name;
    }

    public function writeTitle($key, $data) {
        $index                            = 1;
        $this->handles_sheet_row_mp[$key] = 1;
        foreach ($data as $i => $value) {
            $this->handles_sheet_column_datatype_mp[$key][$i] = $value['data_type'];
            $this->handles_sheet_mp[$key]->setCellValueByColumnAndRow($index, $this->handles_sheet_row_mp[$key], $value['title']);
            $index++;
        }
    }

    public function writeData($key, $data) {
        $index = 1;
        $this->handles_sheet_row_mp[$key]++;
        foreach ($data as $i => $value) {
            $date_type_index = empty($this->handles_sheet_column_datatype_mp[$key][$i]) ? 0 : $this->handles_sheet_column_datatype_mp[$key][$i];
            $data_type       = self::translateDataType($date_type_index);
            $this->handles_sheet_mp[$key]->setCellValueExplicitByColumnAndRow($index, $this->handles_sheet_row_mp[$key], $value, $data_type);
            $index++;
        }
    }

    public function output() {
        foreach ($this->handles as $key => $handle) {
            $objWriter = IOFactory::createWriter($handle, 'Xlsx');
            $objWriter->save($this->handles_filename_mp[$key]);
            /* 释放内存 */
            $handle->disconnectWorksheets();
            unset($handle);
        }
    }

    public function deleteAll() {
        $export_basic_path = $this->getExportBasicPath();
        $this->removeDir($export_basic_path);
    }

    public function toArray() {
        return $this->handles_filename_mp;
    }

    public function getFileExtension() {
        return 'xlsx';
    }

    public static function translateDataType($date_type_index) {
        switch ($date_type_index) {
            case ExportClient::DATA_TYPE_OF_STRING:
                $data_type = DataType::TYPE_STRING;
                break;
            case ExportClient::DATA_TYPE_OF_NUMERIC:
                $data_type = DataType::TYPE_NUMERIC;
                break;
            default:
                $data_type = DataType::TYPE_STRING;
        }
        return $data_type;
    }
}