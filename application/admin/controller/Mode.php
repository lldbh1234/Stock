<?php
namespace app\admin\controller;

use think\Request;
use app\admin\logic\ModeLogic;
use app\admin\logic\ProductLogic;
use app\admin\logic\PluginsLogic;

class Mode extends Base
{
    protected $_logic;
    public function __construct(Request $request = null)
    {
        parent::__construct($request);
        $this->_logic = new ModeLogic();
    }

    public function index()
    {
        $_res = $this->_logic->pageModeLists();
        $this->assign("datas", $_res['lists']);
        $this->assign("pages", $_res['pages']);
        return view();
    }

    public function create()
    {
        if(request()->isPost()){
            exit;
        }
        $products = (new ProductLogic())->allEnableProducts();
        $plugins = (new PluginsLogic())->allEnableModePlugins();
        $this->assign("products", $products);
        $this->assign("plugins", $plugins);
        return view();
    }
}