<?php
namespace app\admin\logic;


use app\admin\model\Deposit;

class DepositLogic
{
    public function pageDepositLists($pageSize = null)
    {
        $pageSize = $pageSize ? : config("page_size");
        $lists = Deposit::order("sort,id")->paginate($pageSize);
        return ["lists" => $lists->toArray(), "pages" => $lists->render()];
    }

    public function depositLists()
    {
        $lists = Deposit::where(["status" => 0])->order("sort,id")->select();
        return $lists ? collection($lists)->toArray() : [];
    }

    public function createDeposit($data)
    {
        $res = Deposit::create($data);
        $pk = model("Deposit")->getPk();
        return $res ? $res->$pk : 0;
    }

    public function depositById($id)
    {
        $deposit = Deposit::find($id);
        return $deposit ? $deposit->toArray() : [];
    }

    public function updateDeposit($data)
    {
        return Deposit::update($data);
    }

    public function deleteDeposit($id)
    {
        $ids = is_array($id) ? implode(",", $id) : $id;
        return Deposit::destroy($ids);
    }
}