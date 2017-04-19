<?php

class User extends ODataSchemeEntity{
    function __construct() {
        parent::__construct(__CLASS__);
        
        $this->addAssociation(new ODataSchemeEntityAssociation(
                "Purchase",
                "PurchaseList",
                ODataSchemeEntityAssociation::MULTIPLE,
                [
                    new ODataSchemeEntityAssociationRelationField("id","userFk")
                ]
        ));
    }
    
    public function security_allowedMethod($method){
        return ODataSchemeEntityGeneralTools::security_readOnly($method);
    }
    
    public function security_visibleField(ODataSchemeEntityField $field,$value){
        return !in_array($field->getName(),["password","active"]);
    }
   
    /*
    public function sequrity_visibleEntity($entity){
        return $entity["active"]==1;
    }
     */
    
    public function query_db_conditions(){
        //$comparator=new ODataQueryFilterComparator();
        //$comparator->init("active","=","1");
        //return ODataQueryFilterAggregator::CreateAndList([$comparator]);
        
        //return ODataQueryFilterAggregator::CreateAndList([ODataQueryFilterComparator::parse("active=1")]);
        
        return ODataFilterParser::parse("active=1");
    }
    
    public function query_db_orderby() {
        return new ODataQueryOrderByList(new ODataQueryOrderBy("name",ODataQueryOrderBy::DESC));
    }

    /*
    public function query_db_limit(){
        return 1;
    } 
     */
    
    /*
    public function query_postprocessList($list){
        foreach ($list as &$item){
            $item["ext"]=rand();
        }
        return $list;
    }
     */
    
    
    public function query_postprocessEntity($entity){
        $entity["ext"]=rand();
        return $entity;
    }
    
    public function query_postprocessField(ODataSchemeEntityField $field, $value){
        if ($field->getName()=="name")
            return strtoupper ($value);
        else
            return $value;
    }
    
    /*
    public function security_transformationFieldOutput(ODataSchemeEntityField $field, $value){
        if ($field->getName()=="id"){
            return ODataCrypt::encrypt($value);
        }else
            return $value;
    }
    
    public function security_transformationFieldInput(ODataSchemeEntityField $field,$value){
        if ($field->getName()=="id"){
            return ODataCrypt::decrypt($value);
        }
        else
            return $value;
    }
     */
}