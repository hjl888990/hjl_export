<?php


namespace Export\Storage;


class ExportStorageAbstract
{

    protected $handles = [];

    protected $export_basic_path = '';

    public function __construct($export_tmp_path) {
        $this->export_basic_path = $this->getExportFilePath($export_tmp_path);//创建导出临时目录
    }

    /**
     * 获取指定文件的句柄
     * @param $key
     * @return mixed|null
     */
    public function getHandle($key) {
        if (!array_key_exists($key, $this->handles)) {
            return null;
        }
        return $this->handles[$key];
    }

    /**
     * 获取本次导出的临时目录
     * @return string
     */
    public function getExportBasicPath() {
        return $this->export_basic_path;
    }

    /**
     * 每个任务创建单独的文件夹
     */
    protected function getExportFilePath($export_tmp_path) {
        $export_tmp_path          = rtrim($export_tmp_path, '/');
        $export_task_tmp_dir_name = uniqid() . posix_getpid();
        $export_basic_path        = $export_tmp_path . '/' . $export_task_tmp_dir_name . '/';
        if (is_dir($export_basic_path)) {//防重复
            $export_basic_path = $this->getExportFilePath($export_tmp_path);
        }
        mkdir($export_basic_path);
        return $export_basic_path;
    }

    /**
     * 删除文件夹
     * @param $dirName
     * @return bool
     */
    protected function removeDir($dirName) {
        if (empty($dirName)) {
            return false;
        }
        if (!is_dir($dirName)) {
            return false;
        }
        $handle = @opendir($dirName);
        while (($file = @readdir($handle)) !== false) {
            if ($file != '.' && $file != '..') {
                $dir = $dirName . '/' . $file;
                is_dir($dir) ? $this->removeDir($dir) : @unlink($dir);
            }
        }
        closedir($handle);
        return rmdir($dirName);
    }

}