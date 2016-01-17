<?php

/**
 * 用来做下拉分页处理数据类
 * @author Jean
 *
 */
class PagerPull extends Pager{
    
    /**
     * 当前从数据库每页数量
     */
    public $realpagesize;
    
    /**
     * 判断是否还有下一页
     * @var unknown
     */
    public $hasnexpage;
    
    /**
     * 是否需要total数据
     */
    public $needtotal;
    
    public function __construct($cp = 1, $pz) {
        $this->curpage = $cp;
        if(isset($pz)){
            $this->pagesize = $pz;
        }
        $this->realpagesize = $this->pagesize + 1;
        $this->start = ($this->curpage-1) * $this->pagesize;
    }
    
    public function setResult($result){
        $this->result = $result;
        $total = count($this->result);
        if($this->realpagesize == $total){
            $this->hasnexpage = true;
            $this->result = array_slice($this->result, 0, $this->pagesize);
        }
    }
    
    public function outputPageJson(){
        return ["curpage" => $this->curpage + 1,
                "hasnexpage" => $this->hasnexpage
        ];
    }
}

?>