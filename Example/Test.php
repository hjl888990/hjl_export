<?php


use Export\ExportClient;

class Test
{
    protected $export_file_handles;

    public function export() {
        $download_url = '';
        $file_name    = '测试文件';
        try {
            $store_type                = 2;//导出文件类型：1 csv;2 excel
            $export_tmp_path           = '/tmp/export';//导出的临时根目录（每个任务会在执行的时候自动创建唯一的子目录）
            $export_storage_factory    = new ExportClient();
            $this->export_file_handles = $export_storage_factory->getExportClient($store_type, $export_tmp_path);

            //初始化指定文件类
            $file_key = 'file1';
            if (!$this->export_file_handles->getHandle($file_key)) {
                $this->export_file_handles->addHandle($file_key);
            }
            //写入标题和格式
            $file_title = [
                ['title' => '标题1(文本)', 'data_type' => 1],
                ['title' => '标题2(数字)', 'data_type' => 2],
                ['title' => '标题3(超链接)', 'data_type' => 3],
            ];
            $this->export_file_handles->writeTitle($file_key, $file_title);
            //持续写入内容
            $row_num  = 10;
            $row_data = ['文本测试', 11111, '多媒体文件/'];
            while ($row_num > 0) {
                $this->export_file_handles->writeData($file_key, $row_data);
                $row_num--;
            }

            //保存文件
            $this->export_file_handles->output();

            $temp_files = $this->export_file_handles->toArray();
            if (count($temp_files) > 1) {
                //文件重命名
                $export_basic_path = $this->export_file_handles->getExportBasicPath();
                $download_file     = $this->zipDirByZipArchive($export_basic_path);
                $object            = $file_name . '.zip';
            } else {
                $download_file = current($temp_files);
                $object        = $file_name . '.' . $this->export_file_handles->getFileExtension();
            }
            //上传到oss
            $download_url = $this->uploadFileToOss($object, $download_file);

        } catch (\Exception $e) {
            //log
        }
        //删除临时文件
        if (!empty($this->export_file_handles)) {
            $this->export_file_handles->deleteAll();
        }
        return $download_url;
    }

    /**
     * 上传到OSS
     *
     * @param string $object
     * @param string $download_file
     * @return string
     */
    protected function uploadFileToOss($object, $download_file) {
        $oss_config      = ['url' => '*****', 'key_id' => '****', 'key_secret' => '***', 'endpoint' => '****', 'bucket' => '******'];
        $oss_pathname    = 'archive/' . rand(0, 999) . '/' . rand(0, 999) . '/' . rand(0, 999) . '/';
        $object          = $oss_pathname . $object;
        $accessKeyId     = $oss_config['key_id'];
        $accessKeySecret = $oss_config['key_secret'];
        $endpoint        = $oss_config['endpoint'];
        $bucket          = $oss_config['bucket'];
        $ossClient       = new OssClient($accessKeyId, $accessKeySecret, $endpoint);
        $ossClient->uploadFile($bucket, $object, $download_file);
        return $oss_config['url'] . '/' . $object;
    }

    /**
     * 通过ZipArchive扩展压缩
     * @param $export_basic_path
     * @return string
     */
    protected function zipDirByZipArchive($export_basic_path) {
        if (empty($export_basic_path) || !is_dir($export_basic_path)) {
            return '';
        }
        $download_file = tempnam($export_basic_path, 'zip');
        $zip           = new \ZipArchive();
        $zip->open($download_file, \ZipArchive::OVERWRITE);
        $download_file_msg = pathinfo($download_file);
        if (empty($download_file_msg['basename'])) {
            return '';
        }
        $this->addFileIntoZipArchive($export_basic_path, $zip, '', [$download_file_msg['basename']]);
        $zip->close();
        return $download_file;
    }

    /**
     * 添加文件到zip
     * @param $dir_path
     * @param $zip
     * @param string $subfolder
     * @param array $exclude_files
     */
    protected function addFileIntoZipArchive($dir_path, &$zip, $subfolder = '', $exclude_files = []) {
        $handle = opendir($dir_path);
        while ($f = readdir($handle)) {
            if ($f == "." || $f == "..") {
                continue;
            }
            if (is_file($dir_path . $f)) {
                $file_msg = pathinfo($dir_path . $f);
                if (empty($file_msg['basename'])) {
                    continue;
                }
                if (in_array($file_msg['basename'], $exclude_files)) {
                    continue;
                }
                if (empty($subfolder)) {
                    $zip->addFile($dir_path . $f, $f);
                } else {
                    $zip->addFile($dir_path . $f, $subfolder . $f);
                }
                continue;
            }
            if (is_dir($dir_path . $f)) {
                $zip->addEmptyDir($subfolder . $f . '/');
                $this->addFileIntoZipArchive($dir_path . $f . '/', $zip, $subfolder . $f . '/', $exclude_files);
                continue;
            }
        }
    }

}