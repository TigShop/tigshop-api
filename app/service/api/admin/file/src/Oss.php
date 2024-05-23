<?php

namespace app\service\api\admin\file\src;

use exceptions\ApiException;
use OSS\OssClient;
use utils\Config;

class Oss
{
    protected object $file;
    protected string $orgPath;
    protected string $filePath;
    protected string $fileName;
    protected string $bucket;
    protected object $ossClient;

    /**
     * 实例化
     * @throws ApiException
     */
    public function __construct()
    {
        $accessKeyId = Config::get('storage_access_key_id');
        $accessKeySecret = Config::get('storage_access_key_secret');
        $bucket = Config::get('storage_bucket');
        $endpoint = Config::get('storage_region');
        if (empty($accessKeyId) || empty($accessKeySecret) || empty($endpoint) || empty($bucket)) {
            throw new ApiException("OSS参数设置错误！");
        }
        $this->bucket = $bucket;
        $this->ossClient = new OssClient($accessKeyId, $accessKeySecret, $endpoint);
    }

    /**
     * 上传文件
     * @return string
     * @throws ApiException
     */
    public function save(): string
    {
        try {
            $this->ossClient->uploadFile($this->bucket, $this->filePath . '/' . $this->fileName, $this->orgPath);
        } catch (\Exception $exception) {
            throw new ApiException('上传图片失败:' . $exception->getMessage());
        }
        return $this->filePath . '/' . $this->fileName;
    }

    /**
     * 设置源文件地址
     * @param $orgPath
     * @return void
     */
    public function setOrgPath($orgPath): void
    {
        $this->orgPath = $orgPath;
    }

    /**
     * 设置文件
     * @param object $file
     * @return void
     */
    public function setFile(object $file): void
    {
        $this->file = $file;
    }

    /**
     * 设置文件上传地址
     * @param string $filePath
     * @return void
     */
    public function setFilePath(string $filePath): void
    {
        $this->filePath = $filePath;
    }

    /**
     * 设置文件名称
     * @param string $fileName
     * @return void
     */
    public function setFileName(string $fileName): void
    {
        $this->fileName = $fileName;
    }
}