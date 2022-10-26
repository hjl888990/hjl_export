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

    protected $use_beautify_style = true;


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
        //美化样式
        if ($this->use_beautify_style) {
            $rowHigh          = 30;
            $fontSize         = 13;
            $fileHandle       = $this->handles[$key]->getHandle();
            $format           = new \Vtiful\Kernel\Format($fileHandle);
            $style            = $format->align(\Vtiful\Kernel\Format::FORMAT_ALIGN_CENTER)->align(\Vtiful\Kernel\Format::FORMAT_ALIGN_VERTICAL_CENTER)
                ->wrap()
                ->bold()
                ->fontSize($fontSize)
                ->toResource();
            $excelColumnIndex = 1;
            $excelRowIndex    = 1;
            foreach ($data as $i => $value) {
                $excelLetter  = $this->numToExcelLetter($excelColumnIndex);
                $column_width = strlen($value['title']) * 1 + 10;
                $this->handles[$key]->setColumn("{$excelLetter}:{$excelLetter}", $column_width);
                $this->handles[$key]->setRow("{$excelLetter}{$excelRowIndex}", $rowHigh, $style);
                $excelColumnIndex++;
            }
        }
    }

    public function writeData($key, $data) {
        $this->handles_sheet_row_mp[$key]++;
        $index = 0;
        foreach ($data as $i => $value) {
            if (strlen($value) > 32000) {//最大长度限制
                $value = substr($value, 0, 32000);
            }
            $date_type = isset($this->handles_sheet_column_datatype_mp[$key][$i]) ? $this->handles_sheet_column_datatype_mp[$key][$i] : 0;
            switch ($date_type) {
                case ExportClient::DATA_TYPE_OF_STRING:
                    $type = 'string';
                    settype($value, $type);
                    $this->handles[$key]->insertText($this->handles_sheet_row_mp[$key], $index, $value);
                    break;
                case ExportClient::DATA_TYPE_OF_NUMERIC:
                    $type = 'float';
                    settype($value, $type);
                    $this->handles[$key]->insertText($this->handles_sheet_row_mp[$key], $index, $value);
                    break;
                case ExportClient::DATA_TYPE_OF_LINK:
                    if (in_array($value, ['该记录无签名', '该记录无图片'])) {
                        $this->handles[$key]->insertText($this->handles_sheet_row_mp[$key], $index, $value);
                    } else {
                        $value      = trim($value, '/');
                        $value      = str_replace('/', '\\', $value);
                        $fileHandle = $this->handles[$key]->getHandle();
                        $format     = new \Vtiful\Kernel\Format($fileHandle);
                        $style      = $format->fontColor(\Vtiful\Kernel\Format::COLOR_BLUE)
                            ->underline(\Vtiful\Kernel\Format::UNDERLINE_SINGLE)
                            ->toResource();
                        $this->handles[$key]->insertUrl($this->handles_sheet_row_mp[$key], $index, $value, $style);
                    }
                    break;
                case ExportClient::DATA_TYPE_OF_WEB_URL:
                    if (substr($value, 0, strlen('http')) === 'http') {
                        $fileHandle = $this->handles[$key]->getHandle();
                        $format     = new \Vtiful\Kernel\Format($fileHandle);
                        $style      = $format->fontColor(\Vtiful\Kernel\Format::COLOR_BLUE)
                            ->underline(\Vtiful\Kernel\Format::UNDERLINE_SINGLE)
                            ->toResource();
                        $this->handles[$key]->insertUrl($this->handles_sheet_row_mp[$key], $index, $value, $style);
                    } else {
                        $type = 'string';
                        settype($value, $type);
                        $this->handles[$key]->insertText($this->handles_sheet_row_mp[$key], $index, $value);
                    }
                    break;
                case ExportClient::DATA_TYPE_OF_STRING_BG_COLOR:
                    $type  = 'string';
                    $color = '';
                    if (!empty($value)) {
                        $value_arr = explode('_', $value);
                        $color     = empty($value_arr[1]) ? '' : $value_arr[1];
                        $value     = empty($value_arr[0]) ? '' : $value_arr[0];
                    }
                    settype($value, $type);
                    if (!empty($color)) {
                        settype($color, 'integer');
                        $fileHandle = $this->handles[$key]->getHandle();
                        $format     = new \Vtiful\Kernel\Format($fileHandle);
                        $style      = $format->background($color)
                            ->toResource();
                        $this->handles[$key]->insertText($this->handles_sheet_row_mp[$key], $index, $value, '', $style);
                    } else {
                        $this->handles[$key]->insertText($this->handles_sheet_row_mp[$key], $index, $value);
                    }
                    break;
                default:
                    $type = 'string';
                    settype($value, $type);
                    $this->handles[$key]->insertText($this->handles_sheet_row_mp[$key], $index, $value);
            }
            $index++;

            //美化样式
            if ($this->use_beautify_style) {
                $rowHigh  = 40;
                $fontSize = 10;
                if (empty($format)) {
                    $fileHandle = $this->handles[$key]->getHandle();
                    $format     = new \Vtiful\Kernel\Format($fileHandle);
                }
                $style            = $format->align(\Vtiful\Kernel\Format::FORMAT_ALIGN_CENTER)->align(\Vtiful\Kernel\Format::FORMAT_ALIGN_VERTICAL_CENTER)
                    ->wrap()
                    ->fontSize($fontSize)
                    ->toResource();
                $excelColumnIndex = $index;
                $excelRowIndex    = $this->handles_sheet_row_mp[$key] + 1;
                $excelLetter      = $this->numToExcelLetter($excelColumnIndex);
                $this->handles[$key]->setRow("{$excelLetter}{$excelRowIndex}", $rowHigh, $style);
            }
            unset($format);
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