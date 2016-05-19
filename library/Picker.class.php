<?php
namespace Swiftx\Libary\ORM;
use Swiftx\ORM\Interfaces\Pickup;
use Swiftx\ORM\Page;

/**
 * -----------------------------------------------------------------------------------------------------------------------------
 * 默认对象拾取器
 * -----------------------------------------------------------------------------------------------------------------------------
 * @author      胡永强 <odaytudio@gmail.com>
 * @since       2014-11-17
 * @copyright   Copyright (c) 2014-2015 Swiftx Inc.
 * -----------------------------------------------------------------------------------------------------------------------------
 */
class Picker extends Pickup {


    /**
     * 获取一个对象
     * @param int $line
     * @return static
     */
    public function Object($line = 1)
    {
        // TODO: Implement Object() method.
    }

    /**
     * 获取多个对象
     * @param null $count
     * @param int $start
     * @return array
     */
    public function Objects($count = null, $start = 1)
    {
        // TODO: Implement Objects() method.
    }

    /**
     * 分页获取对象
     * @param $current
     * @param $number
     * @return Page
     */
    public function Page($current, $number)
    {
        // TODO: Implement Page() method.
    }

}
