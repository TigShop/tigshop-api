<?php

use think\facade\Route;

// 后台的路由结构一定是：目录/控制器/动作/  解析：目录.控制器/动作/
// 如 product/brand/list 会解析为如product.brand/list
// 如 product/list 会解析为如product.product/list

// 注：不支持数字和符号

Route::group('example/example', function () {
    Route::get('list', 'example.example/list'); //示例列表
    Route::post('create', 'example.example/create'); //新增示例
    Route::post('update', 'example.example/update'); //编辑示例
    Route::post('del', 'example.example/del'); //删除示例
});

