<?php
/**
 * 数据分析数据生成
 * @author Jean
 *
 */
class OverviewData{

    const DAY = 'day';
    
    const WEEK = 'week';
    
    const MONTH = 'month';
    
    /**
     * 定义一周内 一月内 时间
     * @var unknown
     */
    
    private $day;
    
    private $week;
    
    private $month;
    
    private $gmtday;
    
    private $gmtweek;
    
    private $gmtmonth;
    
    public function __construct(){
        $day_time = time() - 86400;
        $week_time = time() - (7*86400);
        $month_time = mktime(0,0,0,date('m') - 1,date('d'),date('Y'));
        $this->day = $day_time;
        $this->gmtday = simphp_gmtime($day_time);
        $this->week = $week_time;
        $this->gmtweek = simphp_gmtime($week_time);
        $this->month = $month_time;
        $this->gmtmonth = simphp_gmtime($month_time);
    }
    
    /**
     * 平台商家数
     * @param string $period
     */
    private function getMerchantCount($period = ''){
        $where = "";
        if($period == self::DAY){
            $where .= " and created >= $this->day ";
        }else if($period == self::WEEK){
            $where .= " and created >= $this->week ";
        }else if($period  == self::MONTH){
            $where .= " and created >= $this->month ";
        }
        $sql = "select count(merchant_id) from shp_merchant where activation = 1 $where ";
        return D()->query($sql)->result();
    } 
    
    /**
     * 交易商家数
     * @param string $period
     */
    private function getTradeMerchantCount($period = ''){
        $where = "";
        if($period == self::DAY){
            $where .= " and o.pay_time >= $this->gmtday ";
        }else if($period == self::WEEK){
            $where .= " and o.pay_time >= $this->gmtweek ";
        }else if($period  == self::MONTH){
            $where .= " and o.pay_time >= $this->gmtmonth ";
        }
        $sql = "select count(o.order_id) total,o.merchant_ids from shp_order_info o join shp_merchant m on o.merchant_ids = m.merchant_id 
                where o.is_separate = 0 and o.pay_status = ".PS_PAYED." and m.activation = 1 $where group by o.merchant_ids having total > 0 ";
        return D()->query($sql)->fetch_array_all();
    }
    
    private function getTradeMoney($period = ''){
        $where = "";
        if($period == self::DAY){
            $where .= " and pay_time >= $this->gmtday ";
        }else if($period == self::WEEK){
            $where .= " and pay_time >= $this->gmtweek ";
        }else if($period  == self::MONTH){
            $where .= " and pay_time >= $this->gmtmonth ";
        }
        $sql = "select sum(money_paid) from shp_order_info where is_separate = 0 and pay_status = ".PS_PAYED." $where ";
        return D()->query($sql)->result();
    }
    
    private function get20PercentTradeMoney($period = ''){
        $where = "";
        if($period == self::DAY){
            $where .= " and pay_time >= $this->gmtday ";
        }else if($period == self::WEEK){
            $where .= " and pay_time >= $this->gmtweek ";
        }else if($period  == self::MONTH){
            $where .= " and pay_time >= $this->gmtmonth ";
        }
        $sql = "select sum(money_paid) total,merchant_ids from 
                shp_order_info where is_separate = 0 and pay_status = ".PS_PAYED." $where group by merchant_ids order by total desc ";
        $result = D()->query($sql)->fetch_array_all();
        $pect20 = ceil(count($result) * 0.2);
        $sum = 0;
        for($i = 0; $i<$pect20; $i++){
            $rt = $result[$i];
            $sum += $rt['total'];
        }
        return $sum;
    }
    
    //商家部分数据分析
    public function merchantAnalysis(){
        //商家数量
        $totalMerchant = $this->getMerchantCount();
        $merchantInDay = $this->getMerchantCount(self::DAY);
        $merchantInWeek = $this->getMerchantCount(self::WEEK);
        $merchantInMonth = $this->getMerchantCount(self::MONTH);
        //有过交易的商家数量
        $tradeMch = $this->getTradeMerchantCount();
        $tradeMchInDay = $this->getTradeMerchantCount(self::DAY);
        $tradeMchInWeek = $this->getTradeMerchantCount(self::WEEK);
        $tradeMchInMonth = $this->getTradeMerchantCount(self::MONTH);
        
        $tradeMchCount = count($tradeMch);
        $tradeMchInDayCount = count($tradeMchInDay);
        $tradeMchInWeekCount = count($tradeMchInWeek);
        $tradeMchInMonthCount = count($tradeMchInMonth);
        //商家活跃度
        $mchActive = number_format($tradeMchCount/$totalMerchant, 3);
        $mchActiveInDay = number_format($tradeMchInDayCount/$totalMerchant, 3);
        $mchActiveInWeek = number_format($tradeMchInWeekCount/$totalMerchant, 3);
        $mchActiveInMonth = number_format($tradeMchInMonthCount/$totalMerchant, 3);
        //交易额
        $totalTradeMoney = $this->getTradeMoney();
        $tradeMoneyInDay = $this->getTradeMoney(self::DAY);
        $tradeMoneyInWeek = $this->getTradeMoney(self::WEEK);
        $tradeMoneyInMonth = $this->getTradeMoney(self::MONTH);
        //前20%商家交易额
        $percent20TM = $this->get20PercentTradeMoney('');
        $percent20TMInDay = $this->get20PercentTradeMoney(self::DAY);
        $percent20TMInWeek = $this->get20PercentTradeMoney(self::WEEK);
        $percent20TMInMonth = $this->get20PercentTradeMoney(self::MONTH);
        //前20%商家占比
        $percent20 = number_format($percent20TM/$totalTradeMoney, 3);
        $percent20InDay = number_format($percent20TMInDay/$totalTradeMoney, 3);
        $percent20InWeek = number_format($percent20TMInWeek/$totalTradeMoney, 3);
        $percent20InMonth = number_format($percent20TMInMonth/$totalTradeMoney, 3);
        
        return array('totalMch' => $totalMerchant,'merchantInDay'=>$merchantInDay,'totalMchWeek' => $merchantInWeek,'totalMchMonth' => $merchantInMonth,
            'mchActive' => ($mchActive * 100).'%','mchActiveInDay'=>($mchActiveInDay*100).'%', 'mchActiveWeek' => ($mchActiveInWeek*100).'%', 'mchActiveMonth' => ($mchActiveInMonth*100).'%',
            'tradeMch' =>$this->countByTradeTime($tradeMch) ,'tradeMchInDay'=>$this->countByTradeTime($tradeMchInDay), 'tradeMchWeek' => $this->countByTradeTime($tradeMchInWeek), 'tradeMchMonth' => $this->countByTradeTime($tradeMchInMonth),
            'tradeMoney' => $totalTradeMoney,'tradeMoneyInDay'=>$tradeMoneyInDay, 'tradeMoneyWeek' => $tradeMoneyInWeek, 'tradeMoneyMonth' => $tradeMoneyInMonth,
            '20TM' => $percent20TM,'percent20TMInDay'=>$percent20TMInDay, '20TMWeek' => $percent20TMInWeek, '20TMMonth' => $percent20TMInMonth,
            '20P' => ($percent20*100).'%','percent20InDay'=>($percent20InDay*100).'%', '20PWeek' => ($percent20InWeek*100).'%', '20PMonth' => ($percent20InMonth*100).'%'
        );
    }
    
    ////////////////////////////会员逻辑处理
    
    private function getMemberCount($period, $level){
        $where = "";
        if($period == self::DAY){
            $where .= " and reg_time >= $this->day ";
        }else if($period == self::WEEK){
            $where .= " and reg_time >= $this->week ";
        }else if($period  == self::MONTH){
            $where .= " and reg_time >= $this->month ";
        }
        if($level === 0){
            $where .= " and level = 0 ";
        }else if($level > 0){
            $where .= " and level in ($level) ";
        }
        $sql = "select count(*) from shp_users where mobile <> '' $where ";
        return D()->query($sql)->result();
    }
    
    private function getTradeMemberCount($period){
        $where = "";
        if($period == self::DAY){
            $where .= " and o.pay_time >= $this->gmtday ";
        }else if($period == self::WEEK){
            $where .= " and o.pay_time >= $this->gmtweek ";
        }else if($period  == self::MONTH){
            $where .= " and o.pay_time >= $this->gmtmonth ";
        }
        $sql = "SELECT count(o.order_id) total,o.user_id FROM 
                shp_users u join shp_order_info o on u.user_id = o.user_id and u.mobile <> '' 
                and o.is_separate=0 and o.pay_status = 2 $where  group by o.user_id having(total) > 0 ";
        return D()->query($sql)->fetch_array_all();
    }
    private function getCommision($period){
        $where = "";
        if($period == self::DAY){
            $where .= " and paid_time >= $this->gmtday ";
        }else if($period == self::WEEK){
            $where .= " and paid_time >= $this->gmtweek ";
        }else if($period  == self::MONTH){
            $where .= " and paid_time >= $this->gmtmonth ";
        }
        $sql = "select sum(commision) from shp_user_commision where state >= 0 $where ";
        return D()->query($sql)->result();
    }
    private function getCommisionPect20($period){
        $where = "";
        if($period == self::DAY){
            $where .= " and paid_time >= $this->gmtday ";
        }else if($period == self::WEEK){
            $where .= " and paid_time >= $this->gmtweek ";
        }else if($period  == self::MONTH){
            $where .= " and paid_time >= $this->gmtmonth ";
        }
        $sql = "select sum(commision) s,user_id from shp_user_commision where state >= 0 $where group by user_id order by s desc ";
        $result = D()->query($sql)->fetch_array_all();
        $pect20 = ceil(count($result) * 0.2);
        $sum = 0;
        for($i = 0; $i<$pect20; $i++){
            $rt = $result[$i];
            $sum += $rt['s'];
        }
        return $sum;
    }
    /**
     * 会员数据分析
     */
    public function memberAnalysis(){
        //总会员数
        $total = $this->getMemberCount('', null);
        $totalInDay = $this->getMemberCount(self::DAY, null);
        $totalInWeek = $this->getMemberCount(self::WEEK, null);
        $totalInMonth = $this->getMemberCount(self::MONTH, null);
        //米客
        $totalMK = $this->getMemberCount('', 0);
        $totalMKInDay = $this->getMemberCount(self::DAY, 0);
        $totalMKInWeek = $this->getMemberCount(self::WEEK, 0);
        $totalMKInMonth = $this->getMemberCount(self::MONTH, 0);
        //米商
        $totalMS = $this->getMemberCount('', 1);
        $totalMSInDay = $this->getMemberCount(self::DAY, 1);
        $totalMSInWeek = $this->getMemberCount(self::WEEK, 1);
        $totalMSInMonth = $this->getMemberCount(self::MONTH, 1);
        //银牌
        $totalYP = $this->getMemberCount('', 3);
        $totalYPInDay = $this->getMemberCount(self::DAY, 3);
        $totalYPInWeek = $this->getMemberCount(self::WEEK, 3);
        $totalYPInMonth = $this->getMemberCount(self::MONTH, 3);
        //金牌
        $totalJP = $this->getMemberCount('', '4,5');
        $totalJPInDay = $this->getMemberCount(self::DAY, 4);
        $totalJPInWeek = $this->getMemberCount(self::WEEK, 4);
        $totalJPInMonth = $this->getMemberCount(self::MONTH, 4);
        
        $trade = $this->getTradeMemberCount('');
        $tradeInDay = $this->getTradeMemberCount(self::DAY);
        $tradeInWeek = $this->getTradeMemberCount(self::WEEK);
        $tradeInMonth = $this->getTradeMemberCount(self::MONTH);
        //有过交易的会员
        $tradeCount = count($trade);
        $tradeCountInDay = count($tradeInDay);
        $tradeCountInWeek = count($tradeInWeek);
        $tradeCountInMonth = count($tradeInMonth);
        //交易活跃度
        $tradeActivi = $total > 0 ? (number_format($tradeCount/$total, 3) * 100).'%' : 0;
        $tradeActiviInDay = $total > 0 ? (number_format($tradeCountInDay/$total, 3) * 100).'%' : 0;
        $tradeActiviInWeek = $total > 0 ? (number_format($tradeCountInWeek/$total, 3) * 100).'%' : 0;
        $tradeActiviInMonth = $total > 0 ? (number_format($tradeCountInMonth/$total, 3) * 100).'%' : 0;
        //复购率
        $rebuy = $this->countByTradeTime($trade, $total, true);
        $rebuyInDay = $this->countByTradeTime($tradeInDay, $total, true);
        $rebuyInWeek = $this->countByTradeTime($tradeInWeek, $total, true);
        $rebuyInMonth = $this->countByTradeTime($tradeInMonth, $total, true);
        //有过两次及以上的会员数
        $rebuyGT1 = $this->countMch($trade, true);
        $rebuyGT1InDay = $this->countMch($tradeInDay, true);
        $rebuyGT1InWeek = $this->countMch($tradeInWeek, true);
        $rebuyGT1InMonth = $this->countMch($tradeInMonth, true);
        //拨出总佣金
        $commision = $this->getCommision('');
        $commisionInDay = $this->getCommision(self::DAY);
        $commisionInWeek = $this->getCommision(self::WEEK);
        $commisionInMonth = $this->getCommision(self::MONTH);
        //前20%佣金
        $commisionP20 = $this->getCommisionPect20('');
        $commisionP20InDay = $this->getCommisionPect20(self::DAY);
        $commisionP20InWeek = $this->getCommisionPect20(self::WEEK);
        $commisionP20InMonth = $this->getCommisionPect20(self::MONTH);
        //前20%佣金占比
        $commisionPT = $commision > 0 ? (number_format($commisionP20/$commision, 3) * 100).'%' : 0;
        $commisionPTInDay = $commision > 0 ? (number_format($commisionP20InDay/$commision, 3) * 100).'%' : 0;
        $commisionPTInWeek = $commision > 0 ? (number_format($commisionP20InWeek/$commision, 3) * 100).'%' : 0;
        $commisionPTInMonth = $commision > 0 ? (number_format($commisionP20InMonth/$commision, 3) * 100).'%' : 0;
        return array($total,$totalInDay,$totalInWeek,$totalInMonth,$totalMK,$totalMKInDay,$totalMKInWeek,$totalMKInMonth,
            $totalMS,$totalMSInDay,$totalMSInWeek,$totalMSInMonth,$totalYP,$totalYPInDay,$totalYPInWeek,$totalYPInMonth,
            $totalJP,$totalJPInDay,$totalJPInWeek,$totalJPInMonth,$tradeActivi,$tradeActiviInDay,$tradeActiviInWeek,$tradeActiviInMonth,
            $tradeCount,$tradeCountInDay,$tradeCountInWeek,$tradeCountInMonth,$rebuy,$rebuyInDay,$rebuyInWeek,$rebuyInMonth,
            $rebuyGT1,$rebuyGT1InDay,$rebuyGT1InWeek,$rebuyGT1InMonth,
            $commision,$commisionInDay,$commisionInWeek,$commisionInMonth,$commisionPT,$commisionPTInDay,$commisionPTInWeek,$commisionPTInMonth,
            $commisionP20,$commisionP20InDay,$commisionP20InWeek,$commisionP20InMonth
        );
    }
    //////////////////////////////////////////////////////////平台收入
    
    private function getOrderMoney($period, $orderFlag){
        $where = "";
        if($period == self::DAY){
            $where .= " and pay_time >= $this->gmtday ";
        }else if($period == self::WEEK){
            $where .= " and pay_time >= $this->gmtweek ";
        }else if($period  == self::MONTH){
            $where .= " and pay_time >= $this->gmtmonth ";
        }
        if($orderFlag === 0){
            $where .= " and order_flag = 0 ";
            $sql = "select sum(commision) from shp_order_info where pay_status = 2 and is_separate = 0 $where ";
        }else if($orderFlag == 1 || $orderFlag == 2){
            $where .= " and order_flag = $orderFlag ";
            $sql = "select sum(money_paid) from shp_order_info where pay_status = 2 and is_separate = 0 $where ";
        }
        return D()->query($sql)->result();    
    }
    
    private function getCommisionMoney($period, $orderFlag){
        $where = "";
        if($period == self::DAY){
            $where .= " and paid_time >= $this->gmtday ";
        }else if($period == self::WEEK){
            $where .= " and paid_time >= $this->gmtweek ";
        }else if($period  == self::MONTH){
            $where .= " and paid_time >= $this->gmtmonth ";
        }
        $sql = "select sum(commision) from shp_user_commision where state >= 0 and type in ($orderFlag ) $where ";
        return D()->query($sql)->result();
    }
    /**
     * 套餐成本
     * @param unknown $period
     */
    private function getPackageIncome($period = ''){
        $where = "";
        if($period == self::DAY){
            $where .= " and pay_time >= $this->gmtday ";
        }else if($period == self::WEEK){
            $where .= " and pay_time >= $this->gmtweek ";
        }else if($period  == self::MONTH){
            $where .= " and pay_time >= $this->gmtmonth ";
        }
        $sql = "select sum(og.income_price) from shp_order_info o join shp_order_goods og on o.order_id = og.order_id where o.pay_status = 2
                and o.order_flag=9 and o.is_separate = 0 $where ";
        return D()->query($sql)->result();        
    }
    
    public function platformAnalysis(){
        //商品销售佣金
        $gcommision = $this->getCommisionMoney('', "".UserCommision::COMMISSION_TYPE_FX.",".UserCommision::COMMISSION_TYPE_JY."");
        $gcommisionInD = $this->getCommisionMoney(self::DAY, "".UserCommision::COMMISSION_TYPE_FX.",".UserCommision::COMMISSION_TYPE_JY."");
        $gcommisionInW = $this->getCommisionMoney(self::WEEK, "".UserCommision::COMMISSION_TYPE_FX.",".UserCommision::COMMISSION_TYPE_JY."");
        $gcommisionInM = $this->getCommisionMoney(self::MONTH, "".UserCommision::COMMISSION_TYPE_FX.",".UserCommision::COMMISSION_TYPE_JY."");
        //代理佣金
        $dlcommision = $this->getCommisionMoney('', UserCommision::COMMISSION_TYPE_DL);
        $dlcommisionInD = $this->getCommisionMoney(self::DAY, UserCommision::COMMISSION_TYPE_DL);
        $dlcommisionInW = $this->getCommisionMoney(self::WEEK, UserCommision::COMMISSION_TYPE_DL);
        $dlcommisionInM = $this->getCommisionMoney(self::MONTH, UserCommision::COMMISSION_TYPE_DL);
        //入驻佣金
        $rzcommision = $this->getCommisionMoney('', UserCommision::COMMISSION_TYPE_RZ);
        $rzcommisionInD = $this->getCommisionMoney(self::DAY, UserCommision::COMMISSION_TYPE_RZ);
        $rzcommisionInW = $this->getCommisionMoney(self::WEEK, UserCommision::COMMISSION_TYPE_RZ);
        $rzcommisionInM = $this->getCommisionMoney(self::MONTH, UserCommision::COMMISSION_TYPE_RZ);
        //商品销售毛收入 = 所有佣金 - 拨出佣金
        $gpaid = $this->getOrderMoney('', 0) - $gcommision;
        $gpaidInDay = $this->getOrderMoney(self::DAY, 0) - $gcommisionInD;
        $gpaidInWeek = $this->getOrderMoney(self::WEEK, 0) - $gcommisionInW;
        $gpaidInMonth = $this->getOrderMoney(self::MONTH, 0) - $gcommisionInM;
        //套餐成本
        $package = $this->getPackageIncome();
        $packageInD = $this->getPackageIncome(self::DAY);
        $packageInW = $this->getPackageIncome(self::WEEK);
        $packageInM = $this->getPackageIncome(self::MONTH);
        //平台代理毛收入 = 总收入 - 拨出的佣金 - 套餐成本
        $dlpaid = $this->getOrderMoney('', 1) - $dlcommision - $package;
        $dlpaidInDay = $this->getOrderMoney(self::DAY, 1) - $dlcommisionInD - $packageInD;
        $dlpaidInWeek = $this->getOrderMoney(self::WEEK, 1) - $dlcommisionInW - $packageInW;
        $dlpaidInMonth = $this->getOrderMoney(self::MONTH, 1) - $dlcommisionInM - $packageInM;
        //商品入驻毛收入 = 所有支付金额 - 拨出佣金
        $rzpaid = $this->getOrderMoney('', 2) - $rzcommision;
        $rzpaidInDay = $this->getOrderMoney(self::DAY, 2) - $rzcommisionInD;
        $rzpaidInWeek = $this->getOrderMoney(self::WEEK, 2) - $rzcommisionInW;
        $rzpaidInMonth = $this->getOrderMoney(self::MONTH, 2) - $rzcommisionInM;
        //平台毛收入
        $paid = $gpaid + $dlpaid + $rzpaid;
        $paidInDay = $gpaidInDay + $dlpaidInDay + $rzpaidInDay;
        $paidInWeek = $gpaidInWeek + $dlpaidInWeek + $rzpaidInWeek;
        $paidInMonth = $gpaidInMonth + $dlpaidInMonth + $rzpaidInMonth;
        
        return array($gpaid,$gpaidInDay,$gpaidInWeek,$gpaidInMonth,$dlpaid,$dlpaidInDay,$dlpaidInWeek,$dlpaidInMonth,
            $rzpaid,$rzpaidInDay,$rzpaidInWeek,$rzpaidInMonth,$paid,$paidInDay,$paidInWeek,$paidInMonth,
            $gcommision,$gcommisionInD,$gcommisionInW,$gcommisionInM,$dlcommision,$dlcommisionInD,$dlcommisionInW,$dlcommisionInM,
            $rzcommision,$rzcommisionInD,$rzcommisionInW,$rzcommisionInM,$package,$packageInD,$packageInW,$packageInM
        );
    }
    
    /**
     * 统计总数
     * @param unknown $tradeMch
     * @param string $gt1 是否只统计大于1的
     */
    private function countMch($tradeMch, $gt1 = false){
        $total = 0;
        foreach ($tradeMch as $item){
            $t = $item['total'];
            if($gt1){
                if($t < 2){
                    continue;
                }
            }
            $total ++;
        }
        return $total;
    }
    /**
     * 根据交易次数分别统计
     * @param unknown $tradeMch
     * @param unknown $totalRst 是否是返回百分比
     */
    private function countByTradeTime($tradeMch, $totalRst = 0, $isPercent = false){
        $area10 = 0;
        $area50 = 0;
        $area100 = 0;
        $area500 = 0;
        $area500plus = 0;
        foreach ($tradeMch as $item){
            $total = $item['total'];
            if($total >=1 && $total < 10){
                $area10 ++;
            }else if($total >=10 && $total < 50){
                $area50++;
            }else if($total >=50 && $total < 100){
                $area100++;
            }else if($total >=100 && $total < 500){
                $area500++;
            }else if($total >=500){
                $area500plus++;
            }
        }
        if($isPercent){
            if(!$totalRst){
                return "(0%，0%，0%，0%，0%)";
            }
            return "(".(number_format($area10/$totalRst, 3)*100)."%，".(number_format($area50/$totalRst, 3)*100)."%，
                ".(number_format($area100/$totalRst, 3)*100)."%，".(number_format($area500/$totalRst, 3)*100)."%，".(number_format($area500plus/$totalRst, 3)*100)."%)"; 
        }
        return "(".$area10."，".$area50."，".$area100."，".$area500."，".$area500plus.")";
    }
}

?>