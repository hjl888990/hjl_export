<?php

namespace Export\Storage;

use Export\Storage\Csv\Csv;

class MultiCsv implements \Countable, ExportStoragetInterface
{
    protected $handles = [];

    protected $export_basic_path = '';

    public function __construct($export_basic_path) {
        if (!is_dir($export_basic_path)) {
            mkdir($export_basic_path);
        }
        $this->export_basic_path = $export_basic_path;
    }

    public function addHandle($key, $export_relative_path = '') {
        $temp_file           = tempnam($this->export_basic_path . $export_relative_path, 'filename');
        $handle              = new Csv($temp_file, 'a+');
        $this->handles[$key] = $handle;
    }

    public function getHandle($key) {
        if (!array_key_exists($key, $this->handles)) {
            return null;
        }
        return $this->handles[$key];
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
    }

    public function count() {
        return count($this->handles);
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

    public function getExportBasicPath() {
        return $this->export_basic_path;
    }
}