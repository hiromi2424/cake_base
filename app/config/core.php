<?php
/* Debug */
	Configure::write('debug', 2);

/* App Settings */
	Configure::write('App.name', 'Web Application');
	Configure::write('App.encoding', 'UTF-8');
	//Configure::write('App.baseUrl', env('SCRIPT_NAME'));
	Configure::write('App.base','/cake_base/webroot/');
	//Configure::write('Routing.prefixes', array('admin'));

/* Cache */
	//Configure::write('Cache.disable', true);
	//Configure::write('Cache.check', true);

/* Logging */
	Configure::write('log', true);
	define('LOG_ERROR', 2);

/* Session */
	Configure::write('Session.save', 'php');
	//Configure::write('Session.model', 'Session');
	//Configure::write('Session.table', 'cake_sessions');
	//Configure::write('Session.database', 'default');
	Configure::write('Session.cookie', 'CAKEPHP');
	Configure::write('Session.timeout', '120');
	Configure::write('Session.start', true);
	Configure::write('Session.checkAgent', true);

/* Security */
	Configure::write('Security.level', 'medium');
	Configure::write('Security.salt', 'c4wsv5ed6rf57y8hujk87jv6cewdrfct');
	Configure::write('Security.cipherSeed', '203764137065305374987');

/* Asset */
	//Configure::write('Asset.timestamp', true);
	//Configure::write('Asset.filter.css', 'css.php');
	//Configure::write('Asset.filter.js', 'custom_javascript_output_filter.php');

/* Acl */
	Configure::write('Acl.classname', 'DbAcl');
	Configure::write('Acl.database', 'default');

/* PHP5 need */
	date_default_timezone_set('Asia/Tokyo');


/* Cache */
	/* File */
	/*
	Cache::config('default', array(
		'engine' => 'File', //[required]
		'duration'=> 3600, //[optional]
		'probability'=> 100, //[optional]
			'path' => CACHE, //[optional] use system tmp directory - remember to use absolute path
			'prefix' => 'cake_', //[optional]  prefix every cache file with this string
			'lock' => false, //[optional]  use file locking
			'serialize' => true, [optional]
	));
	*/
	/* APC */
	/*
	Cache::config('default', array(
		'engine' => 'Apc', //[required]
		'duration'=> 3600, //[optional]
		'probability'=> 100, //[optional]
		'prefix' => Inflector::slug(APP_DIR) . '_', //[optional]  prefix every cache file with this string
	));
	*/
	/* XCache */
	/*
	Cache::config('default', array(
		'engine' => 'Xcache', //[required]
		'duration'=> 3600, //[optional]
		'probability'=> 100, //[optional]
		'prefix' => Inflector::slug(APP_DIR) . '_', //[optional] prefix every cache file with this string
		'user' => 'user', //user from xcache.admin.user settings
		'password' => 'password', //plaintext password (xcache.admin.pass)
	));
	*/

	/* Memcache */
	/*
	Cache::config('default', array(
		'engine' => 'Memcache', //[required]
		'duration'=> 3600, //[optional]
		'probability'=> 100, //[optional]
		'prefix' => Inflector::slug(APP_DIR) . '_', //[optional]  prefix every cache file with this string
		'servers' => array(
			'127.0.0.1:11211' // localhost, default port 11211
		), //[optional]
		'compress' => false, // [optional] compress data in Memcache (slower, but uses less memory)
	));
	*/

	Cache::config('default', array('engine' => 'File'));
?>