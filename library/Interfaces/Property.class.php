<?php
namespace Swiftx\ORM\Interfaces;
use Swiftx\ORM\Config;
use Swiftx\ORM\Eloquent;

/**
 * ---------------------------------------------------------------------------------------------------------------
 * ORM属性解析器类
 * ---------------------------------------------------------------------------------------------------------------
 * @author		Hismer <odaytudio@gmail.com>
 * @since		2014-12-16
 * @copyright	Copyright (c) 2014-2015 Swiftx Inc.
 * ---------------------------------------------------------------------------------------------------------------
 */
abstract class Property {

    /** @var   */
    protected $object;
    /** @var Config 配置对象 */
    protected $config;
    /** @var array  用户参数 */
    protected $option;

    /**
     * 构造对象
     * @param Eloquent $object
     * @param Config $config
     * @param array $option
     */
    public function __construct(Eloquent $object, Config $config, array $option){
        $this->object = $object;
        $this->config = $config;
        $this->option = $option;
    }

    /**
     * 读取属性值
     * @param array $data
     * @return mixed
     */
    abstract public function Read(array &$data);

    /**
     * 写入属性值
     * @param mixed $param
     */
    abstract public function Write($param);

}