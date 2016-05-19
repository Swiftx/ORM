<?php
namespace Swiftx\ORM;
use Swiftx\DataBase\Picker;

/**
 * 分页对象
 * @author      胡永强 <odaytudio@gmail.com>
 * @since       2015-12-15
 * @copyright   Copyright (c) 2014-2015 Swiftx Inc.
 */
class Page extends \Swiftx\Common\Page {

	/** @var string 模型类名 */
	protected $_class = null;
	/** @var Picker 对象拾取器 */
	protected $_picker = null;

	/**
	 * 获取该模型所有实例
	 * @param int $current
	 * @param int $number
	 * @param string $class
	 * @param Picker $page
	 */
	public function __construct($current, $number, $class, Picker $page){
		parent::__construct($current, $number,function() use ($page){
			return $page->Count;
		},function($current, $number) use ($class, $page){
			$data =  $page->Rows($number, ($current-1)*$number+1);
			foreach($data as &$value)
				$value = call_user_func_array($class.'::Fetch', $value);
			return $data;
		});
	}

}