<?php
/**
 * 数据分析表格数据构建
 * @author Jean
 *
 */
class OverviewBuilder {
    
    public function __construct(){
    }
    
    /**
     * 
     * 生成表格行结构
     */
    public function genTableRow(){
        require 'OverviewData.php';
        $viewdata = new OverviewData();
        $m = $viewdata->merchantAnalysis();
        
        $u = $viewdata->memberAnalysis();
        list($total,$totalInDay,$totalInWeek,$totalInMonth,$totalMK,$totalMKInDay,$totalMKInWeek,$totalMKInMonth,
            $totalMS,$totalMSInDay,$totalMSInWeek,$totalMSInMonth,$totalYP,$totalYPInDay,$totalYPInWeek,$totalYPInMonth,
            $totalJP,$totalJPInDay,$totalJPInWeek,$totalJPInMonth,$tradeActivi,$tradeActiviInDay,$tradeActiviInWeek,$tradeActiviInMonth,
            $tradeCount,$tradeCountInDay,$tradeCountInWeek,$tradeCountInMonth,$rebuy,$rebuyInDay,$rebuyInWeek,$rebuyInMonth,
            $rebuyGT1,$rebuyGT1InDay,$rebuyGT1InWeek,$rebuyGT1InMonth,
            $commision,$commisionInDay,$commisionInWeek,$commisionInMonth,$commisionPT,$commisionPTInDay,$commisionPTInWeek,$commisionPTInMonth,
            $commisionP20,$commisionP20InDay,$commisionP20InWeek,$commisionP20InMonth) = $u;
        
        $p = $viewdata->platformAnalysis();
        list($gpaid,$gpaidInDay,$gpaidInWeek,$gpaidInMonth,$dlpaid,$dlpaidInDay,$dlpaidInWeek,$dlpaidInMonth,
            $rzpaid,$rzpaidInDay,$rzpaidInWeek,$rzpaidInMonth,$paid,$paidInDay,$paidInWeek,$paidInMonth,
            $gcommision,$gcommisionInD,$gcommisionInW,$gcommisionInM,$dlcommision,$dlcommisionInD,$dlcommisionInW,$dlcommisionInM,
            $rzcommision,$rzcommisionInD,$rzcommisionInW,$rzcommisionInM,$package,$packageInD,$packageInW,$packageInM) = $p;
        
        $overview = array(
            '商家' => array(
                array(
                    'label' => '总商家数',
                    'total' => $m['totalMch'],
                    'day'   => $m['merchantInDay'],
                    'week'  => $m['totalMchWeek'],
                    'month' => $m['totalMchMonth']
                ),
                array(
                    'label' => '商家活跃度=有过交易的商家数/总商家数',
                    'total' => $m['mchActive'],
                    'day'   => $m['mchActiveInDay'],
                    'week'  => $m['mchActiveWeek'],
                    'month' => $m['mchActiveMonth']
                ),
                array(
                    'label' => '有过交易的商家数（分别区间是1<= X <10，10<= X <50，5<= X <100，100<= X <500，X >= 500，X是交易次数。）',
                    'total' => $m['tradeMch'],
                    'day'   => $m['tradeMchInDay'],
                    'week'  => $m['tradeMchWeek'],
                    'month' => $m['tradeMchMonth']
                ),
                array(
                    'label' => '总交易额',
                    'total' => $m['tradeMoney'],
                    'day'   => $m['tradeMoneyInDay'],
                    'week'  => $m['tradeMoneyWeek'],
                    'month' => $m['tradeMoneyMonth']
                ),
                array(
                    'label' => '前20%商家交易占比=前20%商家的总交易额/总交易额',
                    'total' => $m['20P'],
                    'day'   => $m['percent20InDay'],
                    'week'  => $m['20PWeek'],
                    'month' => $m['20PMonth']
                ),
                array(
                    'label' => '前20%商家的总交易额',
                    'total' => $m['20TM'],
                    'day'   => $m['percent20TMInDay'],
                    'week'  => $m['20TMWeek'],
                    'month' => $m['20TMMonth']
                )
            ),
            '会员' => array(
                array(
                    'label' => '总会员数',
                    'total' => $total,
                    'day'   => $totalInDay,
                    'week'  => $totalInWeek,
                    'month' => $totalInMonth
                ),
                array(
                    'label' => '米客',
                    'total' => $totalMK,
                    'day'   => $totalMKInDay,
                    'week'  => $totalMKInWeek,
                    'month' => $totalMKInMonth
                ),
                array(
                    'label' => '米商',
                    'total' => $totalMS,
                    'day'   => $totalMSInDay,
                    'week'  => $totalMSInWeek,
                    'month' => $totalMSInMonth
                ),
                array(
                    'label' => '银牌代理',
                    'total' => $totalYP,
                    'day'   => $totalYPInDay,
                    'week'  => $totalYPInWeek,
                    'month' => $totalYPInMonth
                ),
                array(
                    'label' => '金牌代理',
                    'total' => $totalJP,
                    'day'   => $totalJPInDay,
                    'week'  => $totalJPInWeek,
                    'month' => $totalJPInMonth
                ),
                array(
                    'label' => '会员活跃度=有过交易的会员数/总会员数',
                    'total' => $tradeActivi,
                    'day'   => $tradeActiviInDay,
                    'week'  => $tradeActiviInWeek,
                    'month' => $tradeActiviInMonth
                ),
                array(
                    'label' => '有过交易的会员数',
                    'total' => $tradeCount,
                    'day'   => $tradeCountInDay,
                    'week'  => $tradeCountInWeek,
                    'month' => $tradeCountInMonth
                ),
                array(
                    'label' => '会员复销率=有过两次以上购买的会员数/总会员数（分别区间是1<= X <10，10<= X <50，50<= X <100，100<= X <500，X >= 500，X是交易次数。）',
                    'total' => $rebuy,
                    'day'   => $rebuyInDay,
                    'week'  => $rebuyInWeek,
                    'month' => $rebuyInMonth
                ),
                array(
                    'label' => '有过两次以上购买的会员数',
                    'total' => $rebuyGT1,
                    'day'   => $rebuyGT1InDay,
                    'week'  => $rebuyGT1InWeek,
                    'month' => $rebuyGT1InMonth
                ),
                array(
                    'label' => '佣金总拨出',
                    'total' => $commision,
                    'day'   => $commisionInDay,
                    'week'  => $commisionInWeek,
                    'month' => $commisionInMonth
                ),
                array(
                    'label' => '前20%的消费商占比=前20%消费商（米客+代理）的佣金/总佣金',
                    'total' => $commisionPT,
                    'day'   => $commisionPTInDay,
                    'week'  => $commisionPTInWeek,
                    'month' => $commisionPTInMonth
                ),
                array(
                    'label' => '前20%消费商（米客+代理）的佣金',
                    'total' => $commisionP20,
                    'day'   => $commisionP20InDay,
                    'week'  => $commisionP20InWeek,
                    'month' => $commisionP20InMonth
                )
            ),
            '平台' => array(
                array(
                    'label' => '平台总收入',
                    'total' => $paid,
                    'day'   => $packageInD,
                    'week'  => $paidInWeek,
                    'month' => $paidInMonth
                ),
                array(
                    'label' => '商品销售毛收入',
                    'total' => $gpaid,
                    'day'   => $gpaidInDay,
                    'week'  => $gpaidInWeek,
                    'month' => $gpaidInMonth
                ),
                array(
                    'label' => '商品销售拨出的佣金',
                    'total' => $gcommision,
                    'day'   => $gcommisionInD,
                    'week'  => $gcommisionInW,
                    'month' => $gcommisionInM
                ),
                array(
                    'label' => '销售代理资格的毛收入',
                    'total' => $dlpaid,
                    'day'   => $dlpaidInDay,
                    'week'  => $dlpaidInWeek,
                    'month' => $dlpaidInMonth
                ),
                array(
                    'label' => '销售代理资格的套餐成本',
                    'total' => $package,
                    'day'   => $packageInD,
                    'week'  => $packageInW,
                    'month' => $packageInM
                ),
                array(
                    'label' => '销售代理资格拨出的佣金',
                    'total' => $dlcommision,
                    'day'   => $dlcommisionInD,
                    'week'  => $dlcommisionInW,
                    'month' => $dlcommisionInM
                ),
                array(
                    'label' => '商家入驻的毛收入',
                    'total' => $rzpaid,
                    'day'   => $rzpaidInDay,
                    'week'  => $rzpaidInWeek,
                    'month' => $rzpaidInMonth
                ),
                array(
                    'label' => '商家入驻拨出的佣金',
                    'total' => $rzcommision,
                    'day'   => $rzcommisionInD,
                    'week'  => $rzcommisionInW,
                    'month' => $rzcommisionInM
                )
            )
        );
        return $overview;
    }
}

?>