<?php
namespace app\index\validate;

use think\Validate;
use app\index\logic\StockLogic;
use app\index\model\User;

class Optional extends Validate
{
    protected $rule = [
        'ids'       => 'require|array|min:1',
        'code'      => 'require|checkCode',
    ];

    protected $message = [
        'ids.require'   => "请选择要删除的自选股票！",
        'ids.array'     => "请选择要删除的自选股票！",
        'ids.min'       => "请选择要删除的自选股票！",
        'code.require'  => '系统提示:非法操作！',
        'code.checkCode' => '系统提示:非法操作！',
    ];

    protected $scene = [
        'create' => ['code'],
        'remove' => ['ids']
    ];

    protected function checkCode($value, $rule, $data)
    {
        $stock = (new StockLogic())->stockByCode($value);
        if($stock){
            try{
                $userId = isLogin();
                $optional = User::find($userId)->hasManyOptional()->where(["code" => $value])->find();
                return $optional ? "自选股票已添加！" : true;
            }
            catch(\Exception $e)
            {
                return false;
            }
        }
        return false;
    }
}