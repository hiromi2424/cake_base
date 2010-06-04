<?php
class AppController extends Controller {
	var $helpers = array('Form', 'JpForm', 'Time', 'Session', 'Number');
	var $components = array('Transition', 'Session', 'Qdmail', 'Qdsmtp', 'Cookie');
	
	function beforeFilter() {
		return parent::beforeFilter();
	}
	
	function beforeRender() {
		return parent::beforeRender();
	}
	
	function redirectAction($action = 'index') {
		$args = func_get_args();
		array_shift($args);
		$args['action'] = $action;
		return $this->redirect($args);
	}
	
	function forbidden() {
		return $this->redirect(null, 403);
	}
	
	function notFound() {
		return $this->redirect(null, 404);
	}
	
	function _mail($data){
		$mail =& $this->Qdmail;
		$mail->smtp(true);
		$param = array(
			'host'     => 'localhost',
			'port'     => 587,
			'protocol' => 'SMTP_AUTH',
			'from'     => 'user@example.com',
			'user'     => 'user@example.com',
			'pass'     => 'password',
		);
		$mail->smtpServer($param);
		$mail->from('user@example.com');
		$mail->wordwrapAllow(true);
		
		list($element,$data) = each($data);
		$mail->to($data['email']);
		$mail->subject(__($element, true));
		$mail->cakeText(compact('data'), $element);
		
		return $mail->send();
	}
	
	function debug() {
		$args = func_get_args();
		return call_user_func_array('__debug', $args);
	}
}


?>