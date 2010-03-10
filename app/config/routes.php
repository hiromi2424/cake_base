<?php
	// Router::defaults(false); // No default routing

	Router::connect('/', array('controller' => 'pages', 'action' => 'display', 'home'));
//  Router::connect('/', array('controller' => 'users', 'action' => 'index'));
?>