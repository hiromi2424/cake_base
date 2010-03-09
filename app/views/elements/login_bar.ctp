<?php

if(isset($currentUser)){
	echo $this->Html->tag('span',sprintf(__('You are %s',true),$currentUser['name']),array('id' => 'hoge'));
	echo " | ";
	echo $html->link(__('Account Setting',true),action('users','config'));
	echo " | ";
	echo $html->link(__('Logout',true),action('users','logout'),null,__('Really logout?',true));
}else{
	// echo $html->link(__('Login',true),action('users','login'));
}
