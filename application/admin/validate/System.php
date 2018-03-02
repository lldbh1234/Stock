<?php
namespace app\admin\validate;

use think\Validate;

class System extends Validate
{
    protected $rule = [
//        'name'      => 'require',
        'alias'     => 'require|unique:config',
        'val'       => 'require',
    ];

    protected $message = [
//        'name.require'      => '系统提示：请设置配置名称！',
        'alias.require'     => '系统提示：请设置配置别名！',
        'alias.unique'      => '系统提示：别名已存在！',
        'val.require'       => '系统提示：请设置配置参数！！',
    ];

    protected $scene = [
        'create' => ['alias', 'val'],
    ];

}