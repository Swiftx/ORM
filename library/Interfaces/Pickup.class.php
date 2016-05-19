<?php
namespace Swiftx\ORM\Interfaces;
use Swiftx\DataBase\Picker;
use Swiftx\ORM\Exception;
use Swiftx\ORM\Page;
use Swiftx\ORM\Config;
use Swiftx\System\Object;

/**
 * 对象查询器
 * @author		Hismer <odaytudio@gmail.com>
 * @since		2014-12-16
 * @copyright	Copyright (c) 2014-2015 Swiftx Inc.
 *
 * @property $this $Distinct
 *
 */
abstract class Pickup extends Object {

    /** @var Config 配置对象 */
    protected $config = null;
    /** @var Picker 数据拾取器 */
    protected $picker = null;

    /**
     * 构造对象
     * @param Config $config
     */
    public function __construct(Config $config){
        $this->config = $config;
        $this->picker = $config->DataDriver->NewPicker();
    }

    /**
     * 消除重复项
     * @return $this
     */
    protected function _getDistinct(){
        $this->picker->Distinct;
        return $this;
    }

    /**
     * 获取一个对象
     * @return static
     */
    protected function _getObject(){
        return $this->Object();
    }

    /**
     * 获取所有对象
     * @return array
     */
    protected function _getObjects(){
        return $this->Objects();
    }

    /**
     * 获取一个对象
     * @param int $line
     * @return static
     */
    abstract public function Object($line=1);

    /**
     * 获取多个对象
     * @param null $count
     * @param int $start
     * @return array
     * @throws Exception
     */
    public function Objects($count=null, $start=0){
        $table = $this->picker;
        foreach($this->config->Primary as $name => $value){
            if(empty($value['DataAccess']))
                throw new Exception('设置DataAccess映射值的属性才能作为主键！',500);
            $table->Select($value['DataAccess']);
        }
        if($count)
            $table->Limit($start, $count);
        $Data = $table->Rows;
        foreach($Data as &$value)
            $value = call_user_func_array($this->config->Class.'::Fetch', $value);
        return $Data;
    }

    /**
     * 分页获取对象
     * @param int $current
     * @param int $number
     * @return Page
     * @throws Exception
     */
    public function Page($current, $number){
        foreach($this->config->Primary as $name => $value){
            if(empty($value['DataAccess']))
                throw new Exception('设置DataAccess映射值的属性才能作为主键！',500);
            $this->picker->Select($value['DataAccess']);
        }
        return new Page($current, $number, $this->config->Class, $this->picker);
    }

}