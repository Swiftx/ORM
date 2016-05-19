<?php
namespace Swiftx\ORM;
use Swiftx\System\Object;
use Swiftx\DataBase\Adapter\Official;
use Swiftx\DataType\String;
use Swiftx\DataBase\Interfaces\Driver;
/**
 * ---------------------------------------------------------------------------------------------------------------------
 * 系统异常类
 * ---------------------------------------------------------------------------------------------------------------------
 * @author		Hismer <odaytudio@gmail.com>
 * @since		2014-12-16
 * @copyright	Copyright (c) 2014-2015 Swiftx Inc.
 * ---------------------------------------------------------------------------------------------------------------------
 * @property-read string Class       模型类名
 * @property-read Config Parent      父类配置
 * @property-read array Primary      对象主键
 * @property-read array Column       栏目字段
 * @property-read Driver DataDriver  数据驱动
 * @property-read string TableName   数据表名
 * @property-read array Picker       对象拾取器
 * ---------------------------------------------------------------------------------------------------------------------
 */
class Config extends Object {

    /** @var array 配置项 */
    protected $_option = array();
    /** @var string 模型类名 */
    protected $_classname = null;

    /**
     * 构造函数
     * @param array  $data
     * @param string $classname
     */
    public function __construct(array $data, $classname){
        $this->_option = $data;
        $this->_classname = $classname;
    }

    /**
     * 获取父配置
     * @return Config
     * @throws Exception
     */
    protected function _getParent(){
        $class = new \ReflectionClass($this->_classname);
        $class = $class->getParentClass();
        /** @var Eloquent $classname */
        $classname = $class->name;
        return $classname::Config();
    }

    /**
     * 模型类名
     * @return string
     */
    protected function _getClass(){
        return $this->_classname;
    }

    /**
     * 数据表名
     * @return string
     */
    protected function _getTableName(){
        return $this->_option['System.DataBase']['Table'];
    }

    /**
     * 获取驱动对象
     * @return Driver
     */
    protected function _getDataDriver(){
        /** @var Official $classname */
        $classname = str_replace('.','\\',$this->_option['System.DataBase']['Driver']);
        return $classname::Driver();
    }

    /**
     * 获取主键配置
     * @return array
     */
    protected function _getPrimary(){
        $keys = explode(',',$this->_option['System.DataBase']['Primary']);
        foreach($keys as $value) $result[$value] = $this->Property($value);
        return isset($result)?$result:array();
    }

    /**
     * 获取数据库配置
     * @return mixed
     */
    protected function _getDataBase(){
        return $this->_option['System.DataBase'];
    }

    /**
     * 读取列配置
     * @param $name
     * @throws Exception
     * @return null
     */
    public function Property($name){
        if(array_key_exists('Property.'.$name, $this->_option))
            return $this->_option['Property.'.$name];
        if($this->Parent == null)
            throw new Exception('Config Property '.$name.' is Undefined！');
        return $this->Parent->Property($name);
    }

    /**
     * 读取工厂配置
     * @param $name
     * @return null
     */
    public function Factory($name){
        if(array_key_exists('Factory.'.$name, $this->_option))
            return $this->_option['Factory.'.$name];
        if($this->Parent == null) return null;
        return $this->Parent->Factory($name);
    }

    /**
     * 获取拾取器配置
     * @return array
     */
    protected function _getPicker(){
        if(array_key_exists('System.Picker', $this->_option))
            return $this->_option['System.Picker'];
        else return ['Driver' => 'Swiftx.ORM.Picker'];
    }

}