<?php
/*
* The MIT License
* http://creativecommons.org/licenses/MIT/
*
*  PhpOData (github.com/ilausuch/PhpOData)
* Copyright (c) 2016 Ivan Lausuch <ilausuch@gmail.com>
*/

/**
 * Description of ODataSchemePrimitive
 * Ref: http://docs.oasis-open.org/odata/odata/v4.0/errata03/os/complete/part3-csdl/odata-v4.0-errata03-os-part3-csdl-complete.html#_Structured_Types
 * @author ilausuch
 */
class ODataSchemePrimitive {
    /**
     * Binary data
     */
    const Binary="Edm.Binary";
    
    /**
     * Binary-valued logic
     */
    const Boolean="Edm.Boolean";
    
    /**
     * Unsigned 8-bit integer
     */
    const Byte="Edm.Byte";
    
    /**
     * Date without a time-zone offset
     */
    const Date="Edm.Date";
    
    /**
     * Date and time with a time-zone offset, no leap seconds
     */
    const DateTimeOffset="Edm.DateTimeOffset";
    
    /**
     * Numeric values with fixed precision and scale
     */
    const Decimal="Edm.Decimal";
    
    /**
     * IEEE 754 binary64 floating-point number (15-17 decimal digits)
     */
    const Double="Edm.Double";
    
    /**
     * Signed duration in days, hours, minutes, and (sub)seconds
     */
    const Duration="Edm.Duration";
    
    /**
     * 16-byte (128-bit) unique identifier
     */
    const Guid="Edm.Guid";

    /**
     * Signed 16-bit integer
     */
    const Int16="Edm.Int16";
    
    /**
     * Signed 32-bit integer
     */
    const Int32="Edm.Int32";

    /**
     * Signed 64-bit integer
     */
    const Int64="Edm.Int64";
    
    /**
     * Signed 8-bit integer
     */
    const SByte="Edm.SByte";
    
    /**
     * IEEE 754 binary32 floating-point number (6-9 decimal digits)
     */
    const Single="Edm.Single";

    /**
     * Binary data stream
     */
    const Stream="Edm.Stream";

    /**
     * Sequence of UTF-8 characters
     */
    const String="Edm.String";
    
    /**
     * Clock time 00:00-23:59:59.999999999999
     */
    const TimeOfDay="Edm.TimeOfDay";

    /**
     * Abstract base type for all Geography types
     */
    const Geography="Edm.Geography";

    /**
     * A point in a round-earth coordinate system
     */
    const GeographyPoint="Edm.GeographyPoint";
    
    /**
     * Line string in a round-earth coordinate system
     */
    const GeographyLineString="Edm.GeographyLineString";
    
    /**
     * Polygon in a round-earth coordinate system
     */
    const GeographyPolygon="Edm.GeographyPolygon";

    /**
     * Collection of points in a round-earth coordinate system
     */
    const GeographyMultiPoint="Edm.GeographyMultiPoint";

    /**
     * Collection of line strings in a round-earth coordinate system
     */
    const GeographyMultiLineString="Edm.GeographyMultiLineString";

    /**
     * Collection of polygons in a round-earth coordinate system
     */
    const GeographyMultiPolygon="Edm.GeographyMultiPolygon";
    
    /**
     * Collection of arbitrary Geography values
     */
    const GeographyCollection="Edm.GeographyCollection";

    /**
     * Abstract base type for all Geometry types
     */
    const Geometry="Edm.Geometry";

    /**
     * Point in a flat-earth coordinate system
     */
    const GeometryPoint="Edm.GeometryPoint";

    /**
     * Line string in a flat-earth coordinate system
     */
    const GeometryLineString="Edm.GeometryLineString";

    /**
     * Polygon in a flat-earth coordinate system
     */
    const GeometryPolygon="Edm.GeometryPolygon";

    /**
     * Collection of points in a flat-earth coordinate system
     */
    const GeometryMultiPoint="Edm.GeometryMultiPoint";

    /**
     * Collection of line strings in a flat-earth coordinate system
     */
    const GeometryMultiLineString="Edm.GeometryMultiLineString";

    /**
     * Collection of polygons in a flat-earth coordinate system
     */
    const GeometryMultiPolygon="Edm.GeometryMultiPolygon";

    /**
     * Collection of arbitrary Geometry values
     */
    const GeometryCollection="Edm.GeometryCollection";

    
    private $type;
    
    function __construct($type) {
        $this->type=$type;
    }
    
    function getType(){
        return $this->type;
    }
    
    function isNumerical(){
        return in_array($this->type, [
            ODataSchemePrimitive::Byte,
            ODataSchemePrimitive::Decimal,
            ODataSchemePrimitive::Double,
            ODataSchemePrimitive::Int16,
            ODataSchemePrimitive::Int32,
            ODataSchemePrimitive::Int64,
            ODataSchemePrimitive::SByte,
            ODataSchemePrimitive::Single
        ]);
    }
    
    function isFloat(){
        return in_array($this->type, [
            ODataSchemePrimitive::Decimal,
            ODataSchemePrimitive::Double,
            ODataSchemePrimitive::Single
        ]);
    }
    
    function isInteger(){
        return in_array($this->type, [
            ODataSchemePrimitive::Byte,
            ODataSchemePrimitive::Int16,
            ODataSchemePrimitive::Int32,
            ODataSchemePrimitive::Int64,
            ODataSchemePrimitive::SByte,
        ]);
    }
    
    function isString(){
        return in_array($this->type, [
            ODataSchemePrimitive::String
        ]);
    }
}
