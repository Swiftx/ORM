<?php
namespace Swiftx\ORM\Interfaces;
use Swiftx\ORM\Config;
use Swiftx\ORM\Eloquent;
use Swiftx\ORM\Page;

/**
 * ---------------------------------------------------------------------------------------------------------------
 * ORM静态工厂类
 * ---------------------------------------------------------------------------------------------------------------
 * @author		Hismer <odaytudio@gmail.com>
 * @since		2014-12-16
 * @copyright	Copyright (c) 2014-2015 Swiftx Inc.
 * ---------------------------------------------------------------------------------------------------------------
 */
abstract class Factory {

    /** @var Config 配置对象 */
    protected $config;
    /** @var array  用户参数 */
    protected $option;

    /**
     * 构造对象
     * @param Config $config
     */
    public function __construct(Config $config){
        $this->config = $config;
    }

    /**
     * 设置参数
     * @param $name
     * @param $value
     */
    public function Option($name, $value){
        $this->option[$name] = $value;
    }

    /**
     * 获取对象
     * @param array $param
     * @return Eloquent
     */
    abstract public function FetchObject(array $param);

    /**
     * 获取对象数组
     * @param array $param
     * @return array
     */
    abstract public function FetchArray(array $param);

    /**
     * 获取分页对象
     * @param array $param
     * @return Page
     */
    abstract public function FetchPage(array $param);

    /**
     * 将参数解析进模板
     * @param array $param
     * @param string $tpl
     * @return string
     */
    protected function AnalysisParam(array $param, $tpl){
        foreach($param as $key => $value)
            $tpl = str_replace('${' . ($key + 1) . '}', $value, $tpl);
        return $tpl;
    }

    /**
     * 获取模型对象
     * @param array $primary
     * @return Eloquent
     */
    protected function MakeModel(array $primary){
        return call_user_func_array($this->config->Class.'::Fetch', $primary);
    }

}