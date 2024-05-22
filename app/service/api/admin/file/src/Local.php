<?php

namespace app\service\api\admin\file\src;

use app\common\exceptions\ApiException;
use app\common\utils\Util;

class Local
{
    protected object $file;
    protected string $orgPath;
    protected string $filePath;
    protected string $fileName;


    /**
     * 保存文件
     * @return string
     * @throws ApiException
     */
    public function save(): string
    {
        try {
            if (!is_dir(dirname($this->filePath))) {
                Util::createFolder(dirname($this->filePath));
            }
            $this->file->move($this->filePath, $this->fileName);
            return $this->filePath . '/' . $this->fileName;
        } catch (\Exception $exception) {
            throw  new ApiException($exception->getMessage());
        }
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