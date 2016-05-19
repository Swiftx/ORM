<?php
namespace Swiftx\ORM\Factory;
use Swiftx\DataBase\Interfaces\Dialect;
use Swiftx\ORM\Exception;
use Swiftx\ORM\Interfaces\Factory;
use Swiftx\ORM\Eloquent;
use Swiftx\ORM\Page;

/**
 * ---------------------------------------------------------------------------------------------------------------
 * ORM工厂插件，简单拾取器
 * ---------------------------------------------------------------------------------------------------------------
 * @author		Hismer <odaytudio@gmail.com>
 * @since		2014-12-16
 * @copyright	Copyright (c) 2014-2015 Swiftx Inc.
 * ---------------------------------------------------------------------------------------------------------------
 */
class Simple extends Factory {

    /**
     * 获取对象
     * @param array $param
     * @return Eloquent
     */
    public function FetchObject(array $param){

    }

    /**
     * 获取对象数组
     * @param array $param
     * @return array
     */
    public function FetchArray(array $param){
        $option = $this->option;
        $limit = [
            isset($option['Limit.Count'])?$option['Limit.Count']:null,
            isset($option['Limit.Start'])?$option['Limit.Start']:1,
        ];
        unset($option['Limit.Count']);
        unset($option['Limit.Start']);
        $sql = $this->AnalysisSql($param, $option);
        if($limit[0] != null){
            $limit[0] = (int)$this->AnalysisParam($param, $limit[0]);
            $limit[1] = (int)$this->AnalysisParam($param, $limit[1]);
            $sql->Limit($limit[0], $limit[1]);
        }
        $data = $this->config->DataDriver->QueryRows($sql);
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
        $sql = $this->AnalysisSql($param, $this->option['sentence']);
        $count = $this->config->DataDriver->Count($sql);
        $total = floor($count/$param[1]);
        if($count%$param[1] > 0) $total += 1;
        return new Page($param[0],$param[1], $total,function($current, $numPer) use ($sql){
            $sql = $sql.' LIMIT '.($current-1)*$numPer.','.$numPer;
            $data = $this->config->DataDriver->Query($sql);
            $result = array();
            foreach($data as $value)
                $result[] = call_user_func_array('static::Fetch', $value);
            return $result;
        });
    }

    /**
     * 进行参数解析
     * @param array $param
     * @param array $option
     * @return Dialect
     * @throws Exception
     */
    protected function AnalysisSql(array $param, array $option){
        $sql = $this->config->DataDriver->NewSql();
        $sql->Table($this->config->TableName);
        foreach($this->config->Primary as $name => $value){
            if(empty($value['DataAccess']))
                throw new Exception('设置DataAccess映射值的属性才能作为主键！',500);
            $sql->Select($value['DataAccess']);
        }
        foreach($option as $key => $value){
            $key = explode('.', $key);
            $action = 'Sql'.$key[0];
            $this->$action($key[1], $value, $sql, $param);
        }
        return $sql;
    }

    /**
     * 解析OrderBy指令
     * @param string  $name
     * @param string  $value
     * @param Dialect $sql
     * @param array   $param
     */
    protected function SqlOrderBy($name, $value, Dialect &$sql, $param){
        $value = $this->AnalysisParam($param, $value);
        $sql->OrderBy($name, $value);
    }

}