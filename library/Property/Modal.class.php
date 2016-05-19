<?php
namespace Swiftx\ORM\Property;
use Swiftx\ORM\Config;
use Swiftx\ORM\Eloquent;

/**
 * ---------------------------------------------------------------------------------------------------------------
 * ORM属性接口插件，方法获取
 * ---------------------------------------------------------------------------------------------------------------
 * @author		Hismer <odaytudio@gmail.com>
 * @since		2014-12-16
 * @copyright	Copyright (c) 2014-2015 Swiftx Inc.
 * ---------------------------------------------------------------------------------------------------------------
 */
class Modal extends Method {

    /**
     * 构造对象
     * @param Eloquent $object
     * @param Config   $config
     * @param array    $option
     */
    public function __construct(Eloquent $object, Config $config, array $option){
        $option['DataMapper'] .= '.Fetch';
        parent::__construct($object,$config,$option);
    }

}