<?php
namespace app\web\logic;

use app\web\model\Danger;

class DangerLogic
{
    // 高危股票代码
    public function dangerCodes()
    {
        return Danger::column("code");
    }
}