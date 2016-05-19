<?php
namespace Swiftx\ORM\Factory;
use Swiftx\ORM\Config;
use Swiftx\ORM\Interfaces\Factory;
use Swiftx\ORM\Eloquent;
use Swiftx\ORM\Page;

/**
 * ---------------------------------------------------------------------------------------------------------------
 * ORM工厂插件，Sql拾取器
 * ---------------------------------------------------------------------------------------------------------------
 * @author		Hismer <odaytudio@gmail.com>
 * @since		2014-12-16
 * @copyright	Copyright (c) 2014-2015 Swiftx Inc.
 * ---------------------------------------------------------------------------------------------------------------
 */
class Sql extends Factory {

    /** @var array 预定义参数 */
    protected $const = array();

    /**
     * 构造对象
     * @param Config $config
     */
    public function __construct(Config $config){
        parent::__construct($config);
        $this->const['table'] = $config->TableName;
    }

    /**
     * 获取对象
     * @param array $param
     * @return Eloquent
     */
    public function FetchObject(array $param){
        $sql = $this->AnalysisSql($param, $this->option['Sentence']);
        $result = $this->config->DataDriver->Query($sql);
        if(count($result) == 0) return null;
        return $this->MakeModel($result[0]);
    }

    /**
     * 获取对象数组
     * @param array $param
     * @return array
     */
    public function FetchArray(array $param){
        $sql = $this->AnalysisSql($param, $this->option['Sentence']);
        $data = $this->config->DataDriver->Query($sql);
        $result = array();
        foreach($data as $value)
            $result[] = $this->MakeModel($value);
        return $result;
    }

    /**
     * 获取分页对象
     * @param array $param
     * @return Page
     */
    public function FetchPage(array $param){
        $sql = $this->AnalysisSql($param, $this->option['Sentence']);
        $count = $this->config->DataDriver->Count($sql);
        $total = floor($count/$param[1]);
        if($count%$param[1] > 0) $total += 1;
        return new Page($param[0],$param[1], $total,function($current, $numPer) use ($sql){
            $sql = $sql.' LIMIT '.($current-1)*$numPer.','.$numPer;
            $data = $this->config->DataDriver->Query($sql);
            $result = array();
            foreach($data as $value)
                $result[] = $this->MakeModel($value);
            return $result;
        });
    }

    /**
     * 将参数解析进Sql模板
     * @param array $param
     * @param string $tpl
     * @return string
     */
    protected function AnalysisSql(array $param, $tpl){
        foreach($param as $key => $value) {
            $value = is_string($value)? '\''.addslashes($value).'\'':$value;
            $tpl = str_replace('${' . ($key + 1) . '}', $value, $tpl);
        }
        foreach($this->const as $key => $value)
            $tpl = str_replace('${' . $key . '}', $value, $tpl);
        return $tpl;
    }

}