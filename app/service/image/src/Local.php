<?php

namespace app\service\image\src;

use app\common\utils\Util;
use Intervention\Image\ImageManagerStatic;
use think\File\UploadedFile;

class Local
{
    protected object|string $image;
    protected string $orgPath;
    protected string $filePath;    //如 img/item/202301/example.jpg
    protected bool $watermark = false;
    protected object $ImageManager;
    protected string|null $url = null;

    public function __construct()
    {
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
     * 设置文件地址
     * @param string $filePath
     * @return void
     */
    public function setFilePath(string $filePath): void
    {
        $this->filePath = $filePath;
    }

    /**
     * 初始化
     * @return void
     */
    public function init(): void
    {
        // 从请求中获取上传的图片
        if ($this->image instanceof UploadedFile) {
            $this->ImageManager = ImageManagerStatic::make($this->image->getRealPath());
        } // 如果传入的是图片地址，则直接打开图片
        elseif (is_string($this->image)) {
            $this->ImageManager = ImageManagerStatic::make($this->image);
        }
        if (!is_dir(dirname($this->filePath))) {
            Util::createFolder(dirname($this->filePath));
        }
    }

    /**
     * 保存图片
     * @return string
     */
    public function save(): string
    {
        $this->init();
        $this->ImageManager->save(public_path() . $this->filePath);
        $this->url = $this->filePath;
        return $this->url;
    }

    /**
     * 创建缩略图
     * @param int $width
     * @param int $height
     * @return string
     */
    public function makeThumb(int $width = 0, int $height = 0): string
    {
        $this->init();
        // 生成缩略图
        if ($width > 0 && $height > 0) {
            $this->ImageManager->resize($width, $height);
            $_filePath = explode('.', $this->filePath);
            $_filePath = $_filePath[0] . $width . 'x' . $height . '.' . $_filePath[1];
        }
        $this->ImageManager->save(public_path() . $this->filePath . $_filePath);
        return $_filePath;
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
        $width = $width > 0 ? ',h_' . $width : '';
        $height = $height > 0 ? ',h_' . $height : '';
        return $imageUrl . '?x-oss-process=image/resize,m_lfit' . $width . $height;
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
