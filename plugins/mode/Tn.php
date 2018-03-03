<?php
class Tn
{
    // $day 免息天数
    // $price 当前价格
    // $usage 资金最大使用率
    // $deposit 保证金
    // $lever 杠杆倍数
    // $jiancang 建仓费单位(单位：元/万元)
    // $defer 递延费单位
    public function getTradeInfo($price, $usage, $deposit, $lever, $jiancang, $defer){
        $total = $deposit * $lever; // 申请总配资款 = 保证金 * 杠杆倍数
        $realTotal = $total * $usage / 100; // 实际可使用最大配资款(95%)
        $hand = floor($realTotal / $price / 100) * 100; // 买入股数(整百)
        $jiangcangTotal = $total / 10000 * $jiancang;
        $deferTotal = $total / 10000 * $defer;
        return ["hand" => $hand, 'jiancang' => $jiangcangTotal, 'defer' => $deferTotal];
    }
}