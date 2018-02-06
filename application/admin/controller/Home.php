<?php
namespace app\admin\controller;

use think\Controller;
use think\Request;

class Home extends Controller
{
    public function __construct(Request $request = null)
    {
        parent::__construct($request);
    }

    public function login()
    {
        return "admin login";
        /*if(request()->isPost()){

        }
        return view("login");*/
    }
}