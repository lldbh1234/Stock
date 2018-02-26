<?php
namespace app\index\controller;

use think\Request;
use think\Controller;

class Base extends Controller
{
    public function __construct(Request $request = null)
    {
        parent::__construct($request);
    }
}