<?php
//**---------------------------------------------------------------------+
//**   引导文件
//**---------------------------------------------------------------------+
//**   版权所有：江西佰商科技有限公司. 官网：https://www.tigshop.com
//**---------------------------------------------------------------------+
//**   作者：老杨 yq@lyecs.com
//**---------------------------------------------------------------------+
//**   提示：Tigshop商城系统为非免费商用系统，未经授权，严禁使用、修改、发布
//**---------------------------------------------------------------------+

header('Access-Control-Allow-Headers:Authorization');
//加载通用文件
//管理员验证

//要删

$_SESSION['admin_id'] = 1;
$_SESSION['store_id'] = 0;
$_REQUEST = input();
function test($arr, $is_die = true)
{
    echo "<pre>";
    echo print_r($arr);
    if ($is_die) {
        die();
    }
}