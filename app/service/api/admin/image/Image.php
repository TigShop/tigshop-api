<?php

namespace app\service\api\admin\image;

use app\common\exceptions\ApiException;
use app\common\utils\Config;
use app\common\utils\Time;
use app\service\api\admin\image\src\Cos;
use app\service\api\admin\image\src\Local;
use app\service\api\admin\image\src\Oss;
use think\Exception;
use think\File\UploadedFile;

/**
 * Class Image
 */
class Image
{
    protected object $storageClass;
    protected string $orgPath;
    protected string $filePath;
    protected object|string $image;
    public string $orgName;
    //限制类型
    protected array $limit_ext = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'tif'];

    public function __construct(UploadedFile|string $image = '', $nodePathName = 'upload', $rootPathName = 'img')
    {
        $storage_type = Config::get('storage_type');
        switch ($storage_type) {
            case 0:
                //本地上传
                $this->storageClass = app(Local::class);
                break;
            case 1:
                //Oss上传
                $this->storageClass = app(Oss::class);
                break;
            case 2:
                //Cos上传
                $this->storageClass = app(Cos::class);
                break;
        }
        // 从请求中获取上传的图片
        if ($image instanceof UploadedFile) {
            $extension = $image->getOriginalExtension();
            if (!in_array($extension, $this->limit_ext)) {
                throw new ApiException('上传文件类型错误');
            }
            $this->orgName = pathinfo($image->getOriginalName(), PATHINFO_FILENAME);
            $this->storageClass->setOrgPath($image->getRealPath());
        } // 如果传入的是图片地址
        elseif (is_string($image)) {
            $extension = pathinfo($image, PATHINFO_EXTENSION);
            if (!in_array($extension, $this->limit_ext) && !empty($image)) {
                throw new ApiException('上传文件类型错误');
            }
            $this->orgName = pathinfo($image, PATHINFO_FILENAME);
            $this->storageClass->setOrgPath($image);
        }
        // 过滤特殊字符
        $this->orgName = preg_replace('/[^\x{4E00}-\x{9FFF}a-zA-Z0-9_\.]/u', '', strip_tags($this->orgName));

        $filePath = $rootPathName . '/' . $nodePathName . '/' . date('Ym') . '/' . $this->randomFileName() . '.' . $extension;

        $this->storageClass->setImage($image);
        $this->storageClass->setFilePath($filePath);
    }

    /**
     * 获取一个不重复的名称
     * @return string
     */
    protected function randomFileName(): string
    {
        $pattern = '1234567890abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLOMNOPQRSTUVWXYZ';
        $str = '';
        for ($i = 0; $i < 18; $i++) {
            $str .= $pattern[mt_rand(0, strlen($pattern) - 1)];
        }
        return Time::now() . $str;
    }

    /**
     * 保存图片
     * @return string
     * @throws Exception
     */
    public function save(): string
    {
        return $this->storageClass->save();
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
        return $this->storageClass->makeThumb($width, $height);
    }

    /**
     * 获取缩略图
     * @param string $imageUrl
     * @param int $width
     * @param int $height
     * @return string
     */
    public function getThumb(string $imageUrl, int $width = 0, int $height = 0): string
    {
        return $this->storageClass->getThumb($imageUrl, $width, $height);
    }

    /**
     * 获取url
     * @return string
     */
    public function getUrl(): string
    {
        return $this->storageClass->getUrl();
    }

    public function getStorageUrl(): string
    {
        $storage_type = Config::get('storage_type');
        $url = '';
        switch ($storage_type) {
            case 0:
                $url = Config::get('storage_url');
                break;
            case 1:
                $url = Config::get('storage_oss_url');
                break;
            case 2:
                $url = Config::get('storage_cos_url');
        }
        return $url;
    }
}
