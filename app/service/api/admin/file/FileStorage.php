<?php

namespace app\service\api\admin\file;

use app\common\utils\Config;
use app\common\utils\Time;
use app\service\api\admin\file\src\Local;
use app\service\api\admin\file\src\Oss;
use think\file\UploadedFile;

class FileStorage
{
    protected object $storageClass;

    /**
     * 上传文件
     * @param UploadedFile $file
     * @param int $use_storage_type
     * @param string $rootPathName
     * @param string $fileName
     */
    public function __construct(UploadedFile $file, int $use_storage_type = -1, string $rootPathName = 'upload/file', string $fileName = '')
    {
        $storage_type = Config::get('storage_type');
        if ($use_storage_type > -1) {
            $storage_type = $use_storage_type;
        }
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
                // $result = \Cos::uploadFile($pathname, $params);
                break;
        }
        $extension = $file->getOriginalExtension();
        if (empty($rootPathName)) {
            $filePath = 'upload/file/' . date('Ym');
        } else {
            $filePath = $rootPathName;
        }
        $this->storageClass->setFilePath($filePath);
        if (empty($fileName)) {
            $fileName = $this->randomFileName() . '.' . $extension;
        }
        $this->storageClass->setFileName($fileName);

        $this->storageClass->setOrgPath($file->getRealPath());
        $this->storageClass->setFile($file);
    }


    /**
     * 保存文件
     * @return string
     * @throws \app\common\exceptions\ApiException
     */
    public function save(): string
    {
        return $this->storageClass->save();
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
}