<?php

/**
 * Description of ODataQueryOrderByList
 *
 * @author ilausuch
 */
class ODataQueryOrderByList {
    /**
     *
     * @var ODataQueryOrderBy[]
     */
    private $list;
    
    function __construct(ODataQueryOrderBy $first=null) {
        $this->list=[];
        if ($first!=null)
            $this->list[]=$first;
    }
    
    public function add(ODataQueryOrderBy $orderBy){
        $this->list[]=$orderBy;
    }
    
    /**
     * Returns the list
     * @return ODataQueryOrderBy[]
     */
    public function getList(){
        return $this->list;
    }
    
    /**
     * Parse a string $orderby
     * @param string $str
     * @return \ODataQueryOrderByList
     */
    public static function parse($str){
        $entries=explode(",",$str);
        $list=new ODataQueryOrderByList();
        
        foreach($entries as $entry){
            $item= ODataQueryOrderBy::parse($entry);
            if ($item==null)
                ODataHTTP::error (ODataHTTP::E_bad_request, "Bad sintax on $oderby");
            
            $list->add($item);
        }
        
        if (count($list->getList())==0)
            return null;
        else{
            return $list;
        }
    }
    
    /**
     * Order a list
     * @param object[] $list
     */
    public function sort($list){
        $sortList=$this->getList();
        
        if(count($list)<2)
            return $list;
        
        $left=[];
        $right=[];
        
        reset($list);
        
        $pivot_key  = key($list);
        $pivot  = array_shift($list);
        
        foreach($list as $k => $v) {
            $sortListPos=0;
            
            $allEquivalent=true;
            foreach ($sortList as $sort){
                $order=$sort->compare($v,$pivot);
                if ($order!=0){
                    if ($order>=1)
                        $left[$k] = $v;
                    else
                        $right[$k] = $v;
                    
                    $allEquivalent=false;
                    break;
                }
            }
            if ($allEquivalent)
                $right[$k] = $v;
        }
        return array_merge($this->sort($left), array($pivot_key => $pivot), $this->sort($right));
    }
    
}
