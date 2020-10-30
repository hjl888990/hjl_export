<?php

namespace Export\Storage;

use Export\Storage\Csv\Csv;

class MultiCsv extends ExportStorageAbstract implements \Countable, ExportStoragetInterface
{
    public function __construct($export_tmp_path) {
        parent::__construct($export_tmp_path);
    }

    public function addHandle($key, $export_relative_path = '') {
        $temp_file           = tempnam($this->export_basic_path . $export_relative_path, 'filename');
        $handle              = new Csv($temp_file, 'a+');
        $this->handles[$key] = $handle;
    }

    public function writeTitle($key, $data) {
        $content = array_column($data, 'title');
        $this->handles[$key]->write($content);
    }

    public function writeData($key, $content) {
        $this->handles[$key]->write($content);
    }

    public function output() {
    }

    public function deleteAll() {
        foreach ($this->handles as $handle) {
            $handle->delete();
        }
        $export_basic_path = $this->getExportBasicPath();
        $this->removeDir($export_basic_path);
    }

    public function count() {
    }

    public function toArray() {
        $array = [];
        foreach ($this->handles as $key => $handle) {
            $array[$key] = $handle->getFilename();
        }
        return $array;
    }

    public function getFileExtension() {
        return 'csv';
    }
}