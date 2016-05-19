<?php
namespace Swiftx\ORM\Property;
use Swiftx\ORM\Config;
use Swiftx\ORM\Eloquent;
use Swiftx\ORM\Interfaces\Property;

/**
 * ---------------------------------------------------------------------------------------------------------------
 * ORM属性接口插件，方法获取
 * ---------------------------------------------------------------------------------------------------------------
 * @author		Hismer <odaytudio@gmail.com>
 * @since		2014-12-16
 * @copyright	Copyright (c) 2014-2015 Swiftx Inc.
 * ---------------------------------------------------------------------------------------------------------------
 */
class Method extends Property {

    protected $params = null;
    protected $method = null;

    /**
     * 构造对象
     * @param Eloquent $object
     * @param Config   $config
     * @param array    $option
     */
    public function __construct(Eloquent $object, Config $config, array $option){
        parent::__construct($object,$config,$option);
        $mapper = explode('.', $option['DataMapper']);
        $this->method = '::'.array_pop($mapper);
        $this->method = implode('\\', $mapper).$this->method;
        if(isset($this->option['DataParams'])){
            $this->params = array();
            foreach(explode(',',$this->option['DataParams']) as $value)
                $this->params[] = explode('::',$value);
        }
    }

    /**
     * 读取属性值
     * @param array $data
     * @return mixed
     */
    public function Read(array &$data){
        if($this->params == null)
            return call_user_func($this->method);
        $params = array();
        foreach($this->params as $value)
            $params[] = call_user_func([$this, 'Param'.$value[0]], $value[1], $data);
        return call_user_func_array($this->method,$params);
    }

    /**
     * 解析Table参数
     * @param string $name
     * @param array $data
     * @return
     */
    protected function ParamTable($name, array $data){
        return $data[$name];
    }

    /**
     * 解析Attr参数
     * @param string $name
     * @return mixed
     */
    protected function ParamAttr($name){
        return $this->object->$name;
    }

    /**
     * 写入属性值
     * @param mixed $param
     */
    public function Write($param){
        // TODO: Implement Write() method.
    }

}