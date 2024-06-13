<?php

use think\facade\Route;

// 权限组
Route::group('authority', function () {
    // 管理员日志
    Route::group('admin_log', function () {
        // 列表
        Route::get('list', 'authority.adminLog/list')->append([
            //用于权限校验的名称
            'authorityCheckAppendName' => 'adminLogManage'
        ]);
    });

    // 角色管理
    Route::group('admin_role', function () {
        // 角色列表
        Route::get('list', 'authority.adminRole/list')->append([
            //用于权限校验的名称
            'authorityCheckAppendName' => 'adminRoleManage'
        ]);
        // 角色详情
        Route::get('detail', 'authority.adminRole/detail');
        // 角色添加
        Route::post('create', 'authority.adminRole/create');
        // 角色编辑
        Route::post('update', 'authority.adminRole/update');
        // 角色删除
        Route::post('del', 'authority.adminRole/del');
        // 更新字段
        Route::post('update_field', 'authority.adminRole/updateField');
        // 批量操作
        Route::post('batch', 'authority.adminRole/batch');
    });

    // 管理员
    Route::group('admin_user', function () {
        // 管理员列表
        Route::get('list', 'authority.adminUser/list')->append([
            //用于权限校验的名称
            'authorityCheckAppendName' => 'adminUserManage'
        ]);
        // 指定管理员详情
        Route::get('detail', 'authority.adminUser/detail');
        // 当前管理员详情
        Route::get('mine_detail', 'authority.adminUser/mine_detail');
        // 管理员添加
        Route::post('create', 'authority.adminUser/create');
        // 管理员编辑
        Route::post('update', 'authority.adminUser/update');
        // 管理员删除
        Route::post('del', 'authority.adminUser/del');
        // 配置
        Route::get('config', 'authority.adminUser/config');
        // 更新字段
        Route::post('update_field', 'authority.adminUser/updateField');
        // 批量操作
        Route::post('batch', 'authority.adminUser/batch');
        // 账户修改
        Route::post('modify_manage_accounts', 'authority.adminUser/modifyManageAccounts');
        // 获取验证码
        Route::get('get_code', 'authority.adminUser/getCode');
    });

    // 权限管理
    Route::group('authority', function () {
        // 权限列表
        Route::get('list', 'authority.authority/list')->append([
            //用于权限校验的名称
            'authorityCheckAppendName' => 'authorityManage'
        ]);
        // 权限详情
        Route::get('detail', 'authority.authority/detail');
        // 权限添加
        Route::post('create', 'authority.authority/create');
        // 权限编辑
        Route::post('update', 'authority.authority/update');
        // 获取所有权限
        Route::get('get_all_authority', 'authority.authority/getAllAuthority');
        // 权限删除
        Route::post('del', 'authority.authority/del');
        // 更新字段
        Route::post('update_field', 'authority.authority/updateField');
        // 批量操作
        Route::post('batch', 'authority.authority/batch');
    });

    // 供应商管理
    Route::group('suppliers', function () {
        // 供应商列表
        Route::get('list', 'authority.suppliers/list')->append([
            //用于权限校验的名称
            'authorityCheckAppendName' => 'suppliersManage'
        ]);
        // 供应商详情
        Route::get('detail', 'authority.suppliers/detail');
        // 供应商添加
        Route::post('create', 'authority.suppliers/create');
        // 供应商编辑
        Route::post('update', 'authority.suppliers/update');
        // 供应商删除
        Route::post('del', 'authority.suppliers/del');
        // 更新字段
        Route::post('update_field', 'authority.suppliers/updateField');
        // 批量操作
        Route::post('batch', 'authority.suppliers/batch');
    });

})->middleware([
    \app\adminapi\middleware\CheckAuthor::class
]);