<?php
namespace app\common\model;

use think\Model;

class BaseModel extends Model
{
    const ADMINISTRATOR_ID = 1; //超级管理员
    const ROOT_ID = 2;
    const ADMIN_ROLE_ID = 1; //管理员
    const SETTLE_ROLE_ID = 2; //结算中心
    const OPERATE_ROLE_ID = 3; //运营中心
    const MEMBER_ROLE_ID = 4; //微会员
    const RING_ROLE_ID = 5; //微圈
    const SERVICE_ROLE_ID = 6; //客服
    const FINANCE_ROLE_ID = 7; //财务
}