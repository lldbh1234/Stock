<?php
namespace app\web\controller;

use app\web\logic\OrderLogic;
use app\web\logic\StockLogic;
use app\web\logic\UserFollowLogic;
use app\web\logic\UserLogic;
use app\web\logic\UserNoticeLogic;
use think\Request;

class Index extends Base
{
    public function __construct(Request $request = null)
    {
        parent::__construct($request);
    }

    public function index()
    {
        return view();
    }
}
