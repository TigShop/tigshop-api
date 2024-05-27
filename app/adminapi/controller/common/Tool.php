<?php
//**---------------------------------------------------------------------+
//**   后台控制器文件 --
//**---------------------------------------------------------------------+
//**   版权所有：江西佰商科技有限公司. 官网：https://www.tigshop.com
//**---------------------------------------------------------------------+
//**   作者：Tigshop团队，yq@tigshop.com
//**---------------------------------------------------------------------+
//**   提示：Tigshop商城系统为非免费商用系统，未经授权，严禁使用、修改、发布
//**---------------------------------------------------------------------+

namespace app\adminapi\controller\common;

use app\BaseController;

class Tool extends BaseController
{

    public function __construct()
    {
    }

    public function creat()
    {
        $cfg = [
            'fileName' => 'userAddress', //用于创建 controller/demo.php   tpl/demo/
            'catalog' => 'user', //目录，需提前创建好
            'cnName' => '收货地址', // 中文名称
            'table' => 'user_address',
            'name' => 'address_name',
            'id' => 'address_id',

            'validate_file' => true,
            'validate' => true,
        ];
        $base_path = base_path();
        $controller_file = $base_path . 'adminapi/controller/' . $cfg['catalog'] . '/' . ucfirst($cfg['fileName']) . '.php';
        $model_file = $base_path . 'model/' . $cfg['catalog'] . '/' . ucfirst($cfg['fileName']) . '.php';
        $service_file = $base_path . 'service/' . $cfg['catalog'] . '/' . ucfirst($cfg['fileName']) . 'Service.php';
        $validate_file = $base_path . 'validate/' . $cfg['catalog'] . '/' . ucfirst($cfg['fileName']) . 'Validate.php';

        //检查是否有执行条件
        if (file_exists($controller_file)) {
            die('文件已存在:' . $controller_file);
        }
        if (file_exists($model_file)) {
            die('文件已存在:' . $model_file);
        }
        if (file_exists($service_file)) {
            die('文件已存在:' . $service_file);
        }
        if (file_exists($validate_file)) {
            die('文件已存在:' . $validate_file);
        }
        //创建模板目录、拷贝基础示例文件
        // if (!mkdir ($tpl_dir,0777,true)){
        //     die( '模板文件创建文件失败！');
        // }else{
        //     echo "模板文件夹 ".$tpl_dir.' 创建成功！<br/>';
        // }
        //修改文件
        $replace = array();

        $replace['$table = \'example\';'] = '$table = \'' . $cfg['table'] . '\';';
        $replace['\\example'] = '\\' . $cfg['catalog'];
        $replace['example'] = $cfg['fileName'];
        $replace['example_name'] = $cfg['name'];
        $replace['example_id'] = $cfg['id'];
        $replace['Example'] = ucfirst($cfg['fileName']);
        $replace['示例模板'] = $cfg['cnName'];

        copy($base_path . 'adminapi/controller/example/Example.php', $controller_file);
        file_put_contents($controller_file, strtr(file_get_contents($controller_file), $replace));
        echo "文件: " . $controller_file . ' 创建成功！<br/>';

        copy($base_path . 'model/example/Example.php', $model_file);
        file_put_contents($model_file, strtr(file_get_contents($model_file), $replace));
        echo "文件: " . $model_file . ' 创建成功！<br/>';

        copy($base_path . 'service/example/ExampleService.php', $service_file);
        file_put_contents($service_file, strtr(file_get_contents($service_file), $replace));
        echo "文件: " . $service_file . ' 创建成功！<br/>';

        if ($cfg['validate_file'] == true) {
            if ($cfg['validate'] == false) {
                $replace["        'example_name' => 'require|max:100',\n"] = '';
                $replace["        'inventory_log_name.require' => '商品库存日志名称不能为空',\n"] = '';
                $replace["        'inventory_log_name.max' => '商品库存日志名称最多100个字符',\n"] = '';
            }
            copy($base_path . 'validate/example/ExampleValidate.php', $validate_file);
            file_put_contents($validate_file, strtr(file_get_contents($validate_file), $replace));
            echo "文件: " . $validate_file . ' 创建成功！<br/>';
        }
        die();
    }
}
