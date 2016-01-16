<?php

/**
 * 用来做分页处理数据类
 * @author Jean
 *
 */
class Pager extends CBase {
    
    /**
     * 当前页
     */
    public  $curpage;
    
    /**
     * 当前每页数量
     */
    public $pagesize= 15;
    
    /**
     * 根据curpage跟pagesize计算出start
     */
    public $start;
    
    /**
     * 当前最大页
     */
    public $maxpage;
    
    /**
     * 当前数据总数
     */
    public $totalnum;
    
    /**
     * 当前明细列表
     */
    public $result;
    
    /**
     * 其他数据集合 
     */
    public $otherMap;
    
    public function __construct($curpage = 1, $pagesize) {
        $this->curpage = $curpage;
        if(isset($pagesize)){
            $this->pagesize = $pagesize;
        }
        $this->start = ($this->curpage-1) * $this->pagesize;
    }
    
    public function setTotalNum($totalnum = 0){
        $this->totalnum = $totalnum;
        $maxpage  = ceil($totalnum / $this->pagesize);
        $this->maxpage = $maxpage;
    }
    
    public function outputPageVar($view){
        $view->assign("curpage", $this->curpage);
        $view->assign("maxpage", $this->maxpage);
        $view->assign("totalnum", $this->totalnum);
        $view->assign("pagesize", $this->pagesize);
    }
    
    public function outputPageJson(){
        return ["curpage" => $this->curpage,
                "maxpage" => $this->maxpage,
                "totalnum" => $this->totalnum,
                "pagesize" => $this->pagesize
        ];
    }
}

?>