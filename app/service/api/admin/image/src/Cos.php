<?php

namespace app\service\api\admin\image\src;

use OSS\Core\OssException;
use think\Exception;
use think\File\UploadedFile;
use utils\Config;

class Cos
{
    protected object $cosClient;
    protected string $bucket;
    protected object|string $image;
    protected string $orgPath; //源文件
    protected string $filePath; //如 img/item/202301/example.jpg
    protected bool $watermark = false;
    protected string|null $url = null;

    public function __construct()
    {
        $secretId = Config::get('storage_cos_secret_id');
        $secretKey = Config::get('storage_cos_secret_key');
        $bucket = Config::get('storage_cos_bucket');
        $region = Config::get('storage_cos_region');
        if (empty($secretId) || empty($secretKey) || empty($region) || empty($bucket)) {
            throw new Exception("Cos参数设置错误！");
        }
        $this->bucket = $bucket;
        $client_arr = [
            'region' => $region,
            'credentials' => [
                'secretId' => $secretId,
                'secretKey' => $secretKey
            ]
        ];
        $this->cosClient = new \Qcloud\Cos\Client($client_arr);
    }

    /**
     * 设置文件
     * @param UploadedFile|string $image
     * @return void
     */
    public function setImage(UploadedFile|string $image): void
    {
        $this->image = $image;
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
     * 设置文件地址
     * @param $filePath
     * @return void
     */
    public function setFilePath($filePath): void
    {
        $this->filePath = $filePath;
    }

    /**
     * 保存图片
     * @return string
     * @throws Exception
     */
    public function save(): string
    {
        try {
            if (is_string($this->image)) {
                $data = [
                    'Bucket' => $this->bucket,
                    'Key' => $this->filePath,
                    'Body' => file_get_contents($this->image)];
                $this->cosClient->putObject($data);
            } else {
                $data = [
                    'Bucket' => $this->bucket,
                    'Key' => $this->filePath,
                    'Body' => fopen($this->orgPath, 'rb')];
                $this->cosClient->putObject($data);
            }
        } catch (OssException $e) {
            throw new Exception('上传图片失败:' . $e->getMessage());
        }
        $this->url = $this->filePath;
        return $this->filePath;
    }

    /**
     * 创建缩略图
     * @param int $width
     * @param int $height
     * @return string
     * @throws Exception
     */
    public function makeThumb(int $width = 0, int $height = 0): string
    {
        if (!$this->url) {
            $this->save();
        }
        return $this->filePath . '?imageMogr2/thumbnail/'.$width.'x' . $height;
    }

    /**
     * 获取缩略图地址
     * @param string $imageUrl
     * @param int $width
     * @param int $height
     * @return string
     */
    public function getThumb(string $imageUrl, int $width = 0, int $height = 0): string
    {
        return $this->filePath . '?imageMogr2/thumbnail/'.$width.'x' . $height;
    }

    /**
     * 获取url地址
     * @return string
     */
    public function getUrl(): string
    {
        return $this->url;
    }

}
