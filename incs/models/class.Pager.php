<?php

/**
 * 用来做分页处理数据类
 * @author Jean
 *
 */
class Pager{
    
    private $curpage;
    
    private $pagesize= 15;
    
    private $start;
    
    private $maxpage;
    
    private $totalnum;
    
    private $result;
    
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
    
    /**
	 * magic method '__get'
	 *
	 * @param string $name
	 */
	public function __get($name) {
	    if(isset($this->$name)){
	        return($this->$name);
	    }else{
	        return(NULL);
	    }
	}
	
	/**
	 * magic method '__set'
	 *
	 * @param string $name
	 * @param string $value
	 */
	public function __set($name, $value) {
		$this->$name = $value;
	}
}

?>