<?php
/**
 * Created by PhpStorm.
 * User: hjl
 * Date: 2020/5/22
 * Time: 14:26
 */

namespace Export\Storage;


use Export\ExportClient;

class MultiXlsWriteExcel extends ExportStorageAbstract implements ExportStoragetInterface
{

    protected $handles_filename_mp = [];

    protected $handles_sheet_column_datatype_mp = [];

    protected $handles_sheet_row_mp = [];

    public function __construct($export_tmp_path) {
        parent::__construct($export_tmp_path);
    }

    public function addHandle($key, $export_relative_path = '') {
        $export_file_path = $this->export_basic_path . $export_relative_path;
        $config           = ['path' => $export_file_path];
        $handle           = new \Vtiful\Kernel\Excel($config);
        $tmp_name         = 'filename' . $key . ".xlsx";//临时文件名
        $handle->constMemory($tmp_name);//固定内存模式
        //$handle->fileName($tmp_name, 'sheet1');//普通模式
        $this->handles[$key]             = $handle;
        $this->handles_filename_mp[$key] = $export_file_path . '/' . $tmp_name;
    }

    public function writeTitle($key, $data) {
        $this->handles_sheet_row_mp[$key] = 0;
        foreach ($data as $i => $value) {
            $this->handles_sheet_column_datatype_mp[$key][$i] = $value['data_type'];
        }
        $content = array_column($data, 'title');
        $this->handles[$key]->header($content);
    }

    public function writeData($key, $data) {
        $this->handles_sheet_row_mp[$key]++;
        $index = 0;
        foreach ($data as $i => $value) {
            if ($value === "") {
                $this->handles[$key]->insertText($this->handles_sheet_row_mp[$key], $index, $value);
                $index++;
                continue;
            }
            $date_type = isset($this->handles_sheet_column_datatype_mp[$key][$i]) ? $this->handles_sheet_column_datatype_mp[$key][$i] : 0;
            switch ($date_type) {
                case ExportClient::DATA_TYPE_OF_STRING:
                    $type = 'string';
                    settype($value, $type);
                    $this->handles[$key]->insertText($this->handles_sheet_row_mp[$key], $index, $value);
                    $index++;
                    break;
                case ExportClient::DATA_TYPE_OF_NUMERIC:
                    $type = 'float';
                    settype($value, $type);
                    $this->handles[$key]->insertText($this->handles_sheet_row_mp[$key], $index, $value);
                    $index++;
                    break;
                case ExportClient::DATA_TYPE_OF_LINK:
                    $value      = trim($value, '/');
                    $value      = str_replace('/', '\\', $value);
                    $fileHandle = $this->handles[$key]->getHandle();
                    $format     = new \Vtiful\Kernel\Format($fileHandle);
                    $urlStyle   = $format->fontColor(\Vtiful\Kernel\Format::COLOR_BLUE)
                        ->underline(\Vtiful\Kernel\Format::UNDERLINE_SINGLE)
                        ->toResource();
                    $this->handles[$key]->insertUrl($this->handles_sheet_row_mp[$key], $index, $value, $urlStyle);
                    $index++;
                    break;
                default:
                    $type = 'string';
                    settype($value, $type);
                    $this->handles[$key]->insertText($this->handles_sheet_row_mp[$key], $index, $value);
                    $index++;
            }
        }
    }

    public function output() {
        foreach ($this->handles as $handle) {
            $handle->output();
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
}