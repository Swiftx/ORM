<?php
namespace Swiftx\ORM\Property;
use Swiftx\ORM\Interfaces\Property;

/**
 * ---------------------------------------------------------------------------------------------------------------
 * ORM属性接口插件，表单获取
 * ---------------------------------------------------------------------------------------------------------------
 * @author		Hismer <odaytudio@gmail.com>
 * @since		2014-12-16
 * @copyright	Copyright (c) 2014-2015 Swiftx Inc.
 * ---------------------------------------------------------------------------------------------------------------
 */
class Table extends Property {

    /**
     * 读取属性值
     * @param array $data
     * @return mixed
     */
    public function Read(array &$data){
        $value = $data[$this->option['DataAccess']];
        $method = 'Read'.$this->option['DataMapper'];
        if(!method_exists($this,$method)) return $value;
        return call_user_func([$this,$method], $value);
    }

    /**
     * 转换成Int类型
     * @param $value
     * @return int
     */
    public function ReadInt($value){
        return (int)$value;
    }

    /**
     * 转换成Float类型
     * @param $value
     * @return float
     */
    public function ReadFloat($value){
        return (float)$value;
    }

    /**
     * 转换成浮点类型
     * @param $value
     * @return bool
     */
    public function ReadBool($value){
        return empty($value)?false:true;
    }

    /**
     * 写入属性值
     * @param mixed $param
     */
    public function Write($param){
        // TODO: Implement Write() method.
    }

}