<?php

/**
 * 注：请不要直接给$_SESSION变量赋值！！
 * @author roychen
 */
class Session
{
	private static $_instance;
	private $_expire_duration;
	private $_cookie_key;
	private $_session_data;
	
	private function __construct()
	{
		$this->_expire_duration = 60 * 60 * 24 * 7;
		// 取决于session过期时间
		$this->_cookie_key = md5(date('Ymd', time()));
	}
	
	/**
	 *  获取Session实例
	 *  @return Session
	 */
	public static function get_instance()
	{
		if (self::$_instance === null) 
		{
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	/**
	 *  每次收到http请求时之行，将$_COOKIE中session的值赋值给$_session_data
	 */
	public function init()
	{
		if (!isset($_COOKIE['session']))
		{
			return false;
		}

		$this->_session_data = json_decode(rtrim(mcrypt_decrypt(MCRYPT_RIJNDAEL_256, $this->_cookie_key, $_COOKIE['session'], MCRYPT_MODE_CBC, md5($this->_cookie_key)), "\0"), true);

		return true;
	}

	/**
	 *  设置$_session_data值的函数
	 *  接收类似
	 *  $this->session->set(array('a' => 1, 'b' => 2));
	 *  或者单个赋值
	 *  $this->session->set('a', 1);
	 *  的参数
	 */
	public function set($values, $v = '')
	{
		if (is_array($values))
		{
			foreach ($values as $k => $v)
			{
				$this->_session_data[$k] = $v;
			}
		}
		else
		{
			$this->_session_data[$values] = $v;
		}

		$this->_set_cookie();
	}

	/**
	 *  获取$_session_data值的函数
	 */
	public function get($key)
	{
		if (!isset($this->_session_data[$key]))
		{
			return null;
		}

		return $this->_session_data[$key];
	}

	/*
	 *  获取全部Session
	 */
	public function all()
	{
		return $this->_session_data;
	}

	/*
	 *  销毁Session
	 */
	public function destroy()
	{
		// 清除Session
		foreach ($this->_session_data as $key => $v)
		{
			unset($this->_session_data[$key]);
		}
		
		// 清除Cookie
		setcookie('session', '', time() - $this->_expire_duration, '/');
	}

	/*
	 *  设置cookie
	 *  直接将$_session_data的值加密之后写到cookie里面
	 */
	private function _set_cookie()
	{
		$cookie_str = mcrypt_encrypt(MCRYPT_RIJNDAEL_256, $this->_cookie_key, json_encode($this->_session_data), MCRYPT_MODE_CBC, md5($this->_cookie_key));

		// 将cookie种在根域名下
		setcookie('session', $cookie_str, time() + $this->_expire_duration, '/');
	}
}
