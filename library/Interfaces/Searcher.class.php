<?php
namespace Swiftx\ORM\Interfaces;
use Swiftx\Common\Page;
use Swiftx\DataBase\Picker;
use Swiftx\ORM\Exception;
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
abstract class Searcher extends Object {

    /** @var Picker 数据拾取器 */
    protected $picker = null;

    /**
     * 获取对象配置
     * @param Config $config
     * @throws Exception
     */
    protected function InitByConfig(Config $config){
        $this->picker = $config->DataDriver->NewPicker();
        foreach($config->Primary as $name => $value){
            if(empty($value['DataAccess']))
                throw new Exception('设置DataAccess映射值的属性才能作为主键！',500);
            $this->picker->Select($value['DataAccess']);
        }
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
     * 获取对象方法
     * @param array $params
     * @return
     */
    abstract public function FetchObject(array $params);

    /**
     * 统计查询结果数量
     * @return int
     */
    public function Count(){
        return $this->picker->Count;
    }

    /**
     * 获取一个对象
     * @param int $line
     * @return static
     * @throws Exception
     */
    public function Object($line=1){
        $table = $this->picker;
        $Data = $table->Limit($line, 1)->Rows;
        return $this->FetchObject($Data);
    }

    /**
     * 获取多个对象
     * @param null $count
     * @param int $start
     * @return array
     * @throws Exception
     */
    public function Objects($count=null, $start=0){
        $table = $this->picker;
        if($count) $table->Limit($start, $count);
        $Data = $table->Rows;
        foreach($Data as &$value)
            $value = $this->FetchObject($value);
        reset($Data);
        return $Data;
    }

    /**
     * 分页获取对象
     * @param int $current
     * @param int $number
     * @return Page
     */
    public function Page($current, $number){
        return new Page($current, $number,function(){
            return $this->picker->Count;
        },function($current, $number){
            $data =  $this->picker->Rows($number, ($current-1)*$number+1);
            foreach($data as &$value)
                $value = $this->FetchObject($value);
            return $data;
        });
    }

}