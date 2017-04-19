<?php

require_once("../src/tools/ODataFilterParser.php");
require_once("../src/io/ODataHTTP.php");
require_once("../src/query/ODataQueryFilterBase.php");
require_once("../src/query/ODataQueryFilterAggregator.php");
require_once("../src/query/ODataQueryFilterComparator.php");

var_dump(ODataFilterParser::parse("var1=6 and (name eq 'Ivan' or Id eq 3)"));
