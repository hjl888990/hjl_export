<?php

namespace Export\Storage;

/**
 * Created by PhpStorm.
 * User: hjl
 * Date: 2020/5/21
 * Time: 10:58
 */
interface ExportStoragetInterface
{
    /**
     * 设置导出基本目录
     * ExportStoragetInterface constructor.
     * @param $export_basic_path
     */
    public function __construct($export_basic_path);

    /**
     * 实例化对应导出类
     * @param $key
     * @param $export_relative_path 相对路径
     * @return mixed
     */
    public function addHandle($key, $export_relative_path);

    /**
     * 获取对应导出类
     * @param $key
     * @return mixed
     */
    public function getHandle($key);

    /**
     * 写入标题
     * @param $key
     * @param $content
     * @return mixed
     */
    public function writeTitle($key, $content);

    /**
     * 写入内容
     * @param $key
     * @param $content
     * @return mixed
     */
    public function writeData($key, $content);

    public function output();

    /**
     * 获取文件后缀
     * @return mixed
     */
    public function getFileExtension();

    public function deleteAll();

    public function count();

    /**
     * 获取文件名集合
     * @return mixed
     */
    public function toArray();

    public function getExportBasicPath();

}