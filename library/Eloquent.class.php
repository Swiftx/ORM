<?php
namespace Swiftx\ORM;
use Swiftx\System\Object;
use Swiftx\DataBase\Interfaces\Dialect;
use Swiftx\Libary\ORM\Picker;
use Swiftx\ORM\Interfaces\Factory;
use Swiftx\ORM\Interfaces\Property;
use Swiftx\DataBase\Picker as DbPicker;
/**
 * 模型对象基类
 *
 * @author      胡永强 <odaytudio@gmail.com>
 * @since       2014-11-17
 * @copyright   Copyright (c) 2014-2015 Swiftx Inc.
 */
class Eloquent extends Object {

    /** @var array 对象数据 */
    protected $_data = array();
    /** @var array 字段缓存 */
	protected $_cache = array();
    /** @var bool 是否同步 */
    protected $_sync = true;

    /**
     * 构造函数
     * @param array $data
     */
	protected function __construct($data = null){
        $this->_data = $data;
	}

    /**
     * 数组模式读取一行数据
     * @param string $offset 列名
     * @throws Exception
     * @return mixed
     */
	public function __get($offset){
		// 解析用户请求
        $method = '_get'.$offset;
		if(method_exists($this, $method))
			return $this->$method();
        // 获取配置信息
        $config = static::Config();
        // 配置项不存在
        $option = $config->Property($offset);
        if($option == null)
            throw new Exception($offset.'属性不存在',500);
        // 属性不能读取
        if($option['Visibility'] == 'Write-Only')
            throw new Exception($offset.'属性不可读',500);
        // 获取属性对象
        $IocName = 'Property::'.static::Class.'::'.$offset;
        /** @var Property $property */
        if(Container::Exists($IocName))
            return Container::Fetch($IocName)->Read($this->_data);
        // 默认配置内容
        if(empty($option['DataMapper']))
            $option['DataMapper'] = 'Table::String';
        // 生成处理对象
        $option['DataMapper'] = explode('::',$option['DataMapper']);
        $classname = 'Swiftx\\ORM\\Property\\'.$option['DataMapper'][0];
        $option['DataMapper'] = $option['DataMapper'][1];
        $property = new $classname($this, $config, $option);
        // 注册执行结果
        Container::Instance($IocName, $property, true);
        return $property->Read($this->_data);
	}


    /**
     * 数组模式设置字段的值
     * @param string $offset 列名
     * @param string $value 值
     * @return bool|void
     * @throws Exception
     */
	public function __set($offset, $value){
        // 解析用户请求
        $method = '_set'.$offset;
        if(method_exists($this, $method))
           return $this->$method($value);
        // 获取配置信息
        $config = static::Config();
        // 配置项不存在
        $option = $config->Property($offset);
        if($option == null)
            throw new Exception($offset.'属性不存在',500);
        // 属性不能读取
        if($option['Visibility'] == 'Read-Only')
            throw new Exception($offset.'属性不可写',500);
        // 获取属性对象
        $IocName = 'Property::'.static::Class.'::'.$offset;
        /** @var Property $property */
        if(Container::Exists($IocName))
            return Container::Fetch($IocName)->Read($this->_data);
        // 默认配置内容
        if(empty($option['DataMapper']))
            $option['DataMapper'] = 'Table::String';
        // 生成处理对象
        $option['DataMapper'] = explode('::',$option['DataMapper']);
        $classname = 'Swiftx\\ORM\\Property\\'.$option['DataMapper'][0];
        $option['DataMapper'] = $option['DataMapper'][1];
        $property = new $classname($this, $config, $option);
        // 注册执行结果
        Container::Instance($IocName, $property, true);
        return $property->Write($this->_data);
	}

    /**
     * 保存对象修改
     */
    public function Save(){
        $config = static::Config();
        $database = static::Table();
        foreach ($config->Primary as $value) {
            if (empty($value['DataAccess']))
                throw new Exception('设置DataAccess映射值的属性才能作为主键！', 500);
            $database->Where($value['DataAccess'], $this->_data[$value['DataAccess']]);
        }
        // 构造数据
        $data = array();
        foreach ($this->_cache as $value)
            $data[$value] = $this->_data[$value];
        $database->Update($data);
        $this->_cache = array();
    }

    /**
     * 读写原始数据
     * @param string $key
     * @param bool|string|int|null $data
     * @param bool $sync
     * @return void|string
     * @throws Exception
     */
    protected function Data($key, $data=false, $sync=false){
        if($data === false)
            return $this->_data[$key];
        $this->_data[$key] = $data;
        if($sync == true){
            unset($this->_cache[$key]);
            $config = static::Config();
            $database = static::Table();
            foreach($config->Primary as $value){
                if(empty($value['DataAccess']))
                    throw new Exception('设置DataAccess映射值的属性才能作为主键！',500);
                $database->Where($value['DataAccess'], $this->_data[$value['DataAccess']]);
            }
            $database->Update([$key=>$data]);
        } else if (!in_array($key, $this->_cache)){
            $this->_cache[] = $key;
        }
    }

    /**
     * 读写数据映射
     * @param string $key
     * @param bool $data
     * @param bool $sync
     * @return mixed
     * @throws Exception
     */
    protected function Property($key, $data=false, $sync=false){
        $Property = static::Config()->Property($key);
        $key = $Property['DataAccess'];
        if($data === false) return $this->Data($key);
        $this->Data($key, $data, $sync);
    }

    /**
     * 读取配置对象
     * @throws Exception
     * @return Config
     */
    public static function Config(){
        if(static::class == self::class) return null;
        $IocName = 'Config::'.static::Class;
        if(Container::Exists($IocName))
            return Container::Fetch($IocName);
        $ModalClass = new \ReflectionClass(get_called_class());
        $ConfigFile = substr($ModalClass->getFileName(),0,-9).'modal.ini';
        if(!file_exists($ConfigFile)) throw new Exception('配置不存在', 500);
        $Config = new Config(parse_ini_file($ConfigFile,true),static::class);
        Container::Instance($IocName, $Config, true);
        return $Config;
    }

    /**
     * 根据主键获取对象
     * @throws Exception
     * @return static::class|null
     */
    public static function Fetch(){
        // 获取传入参数
        $keys = func_get_args();
        // Ioc已经注册对象
        $IocName = 'Object::'.static::Class.'::'.implode('-',$keys);
        if(Container::Exists($IocName)) return Container::Fetch($IocName);
        // 未注册过对象
        $sql = static::NewSql();
        $sql->Table(static::Config()->TableName);
        foreach(static::Config()->Primary as $name => $value){
            if(empty($value['DataAccess']))
                throw new Exception('设置DataAccess映射值的属性才能作为主键！',500);
            $sql->Where($value['DataAccess'], current($keys));
            next($keys);
        }
        // 获取数据
        $data = static::Config()->DataDriver->Query($sql->Row);
        if(count($data) > 0) {
            $classname = static::Class;
            $data = new $classname($data[0]);
        }
        // 注册Ioc对象
        Container::Instance($IocName, $data, true);
        return $data;
    }

    /**
     * 获取多条记录
     * @param int|null $num
     * @param int|null $start
     * @throws Exception
     * @return array
     */
    public static function FetchList($num=null, $start=null){
        $sql = static::Table();
        foreach(static::Config()->Primary as $value){
            if(empty($value['DataAccess']))
                throw new Exception('设置DataAccess映射值的属性才能作为主键！',500);
            $sql->Select($value['DataAccess']);
        }
        if($num==null and $start==null)
            $data = $sql->Rows;
        else if($num!=null and $start!=null)
            $data = $sql->Rows($num);
        else if($num!=null and $start!=null)
            $data = $sql->Rows($start, $num);
        else{
            $num = $sql->Count;
            $data = $sql->Rows($start, $num);
        }
        foreach($data as &$value)
            $value = call_user_func_array('static::Fetch', $value);
        return $data;
    }

    /**
     * 获取对象拾取器
     * @return Picker
     */
    public static function NewPicker(){
        $config = static::Config();
        $classname = str_replace('.','\\',$config->Picker['Driver']);
        return new $classname($config);
    }

    /**
     * Sql对象
     * @return Dialect
     */
    public static function NewSql(){
        return static::Config()->DataDriver->NewSql();
    }

    /**
     * 静态魔术方法
     * @param string $name
     * @param array $args
     * @return mixed
     * @throws Exception
     */
    public static function __callStatic($name, $args){
        $config = static::Config()->Factory($name);
        if(!$config) throw new Exception('方法不存在', '501');
        // 获取工厂驱动参数
        $method = explode('::',$config['Method']);
        $classname = str_replace('.','\\',$method[0]);
        $method = 'Fetch'.$method[1];
        unset($config['Method']);
        // Ioc已经注册对象
        $IocName = 'Factory::'.static::class.'::'.$classname.'-'.implode('-',$config);
        /** @var Factory $factory */
        if(Container::Exists($IocName))
            return Container::Fetch($IocName)->$method($args);
        // 生成注册Ioc对象
        $factory = new $classname(static::Config());
        foreach($config as $key => $value)
            $factory->Option($key, $value);
        Container::Instance($IocName, $factory, true);
        return $factory->$method($args);
    }

    /**
     * 获取当前模型的表拾取器
     * @param null|string $name
     * @return DbPicker
     * @throws Exception
     */
    protected static function Table($name=null){
        $picker = static::Config()->DataDriver->NewPicker();
        if($name==null) $name = static::Config()->TableName;
        return $picker->Table($name);
    }

    /**
     * 当前模型表插入数据
     * @return int
     */
    protected static function Insert(){
        $args = func_get_args();
        $method = [static::Table(), 'Insert'];
        return call_user_func_array($method, $args);
    }

    /**
     * 通过主键删除对象
     * @throws Exception
     * @return null
     */
    public static function Delete(){
        // 获取传入参数
        $keys = func_get_args();
        // 构造查询条件
        $table = static::Table();
        foreach(static::Config()->Primary as $name => $value){
            if(empty($value['DataAccess']))
                throw new Exception('设置DataAccess映射值的属性才能作为主键！',500);
            $table->Where($value['DataAccess'], current($keys));
            next($keys);
        }
        // 执行删除操作
        if($table->Delete) return true;
        throw new Exception('删除失败！',500);
    }

}