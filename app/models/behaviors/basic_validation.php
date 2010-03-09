<?php
class BasicValidationBehavior extends ModelBehavior {
	var $loaded = false;
	var $autoConvert = true;
	var $convert = array();
	
	#########################################################################
	/**
	 * エラーメッセージ
	 */
	#########################################################################
	var $validateMessage = array();
	
	function setup(&$Model, $settings = array()){
		$this->validateMessage = array(
			// 標準バリデーション
			'alphaNumeric'  => __('半角英数字で入力してください',true),
			'between'       => __('%s文字以上%2s文字以内の半角文字を入力してください',true),
			'blank'         => __('空でなければなりません',true),
			'cc'            => __('クレジットカード番号として正しくありません',true),
			'custom'        => __('入力値が正しくありません',true),
			'date'          => __('日付形式で入力してください',true),
			'decimal'       => __('小数点第%s位までの半角数字を入力してください。',true),
			'email'         => __('メールアドレスが不正です',true),
			'equalTo'       => __('入力値が%sと一致しません',true),
			'extension'     => __('拡張子が正しくありません',true),
			'ip'            => __('IPアドレス形式で入力してください',true),
			'minLength'     => __('%sバイト文字以上で入力してください',true),
			'maxLength'     => __('%sバイト文字以内で入力してください',true),
			'money'         => __('入力値が正しくありません',true),
			'numeric'       => __('半角数字で入力してください',true),
			'phone'         => __('電話番号形式で入力してください',true),
			'postal'        => __('郵便番号形式で入力してください',true),
			'range'         => __('%sより大きく%sより小さい半角数字を入力してください',true),
			'url'           => __('URL形式で入力してください',true),
			'isUnique'      => __('この値は既に登録済です',true),
			'inList'        => __('入力値が正しくありません',true),
			'time'          => __('時:分 形式で入力してください',true),
		
			// 拡張バリデーション
			'valid_required'    => __('入力してください',true),
			'valid_maxLen'      => __('入力された文字数が制限を越えています(最大%s文字)',true),
			'valid_minLen'      => __('入力された文字数が制限未満です(最小%s文字)',true),
			'valid_equalLen'    => __('入力された文字数が正しくありません(%s文字で入力してください)',true),
			'valid_phone'       => __('電話番号形式で入力してください',true),
			'valid_zip'         => __('郵便番号形式(3桁-4桁)で入力してください',true),
			'valid_zen'         => __('全角以外の文字が含まれています',true),
			'valid_kana'        => __('カタカナ以外の文字が含まれています',true),
			'valid_hirakana'    => __('ひらかな以外の文字が含まれています',true),
			'valid_single'      => __('半角以外の文字が含まれています',true),
			'valid_confirm'     => __('入力内容が一致しません',true),
			'valid_email'       => __('メールアドレスが不正です',true),
			'valid_emailMulti'  => __('メールアドレスが不正です',true),
			'valid_ymd'         => __('正しい日付形式で入力してください',true),
			'valid_jis'         => __('環境依存文字・旧漢字はご利用頂けません',true),
			'valid_min_max'     => __('%s以上%s以下の数字を入力してください。',true),
			
			'passwordMinLength' => __('%s文字以上で入力してください',true),
			'passwordMaxLength' => __('%s文字以下で入力してください',true),
		);
		
		if(isset($Model->validateMessage) && is_array($Model->validateMessage)){
			$this->validateMessage = am($this->validateMessage,$Model->validateMessage);
		}
	}
	
	#########################################################################
	/**
	 * データ整形用にカラムとルールの対応を保存
	 */
	#########################################################################
	function SetConvert(&$model, $col, $rule) {
		$this->convert[][$col] = $rule;
	}
   
	#########################################################################
	/**
	 * バリデーション定義毎のデータ整形
	 */
	#########################################################################
	function convertData(&$model, $col, $rule) {
		$before = '';
		$after = '';
		if(isset($model->data[$model->name]) && isset($model->data[$model->name][$col])){
			$before = $model->data[$model->name][$col];
			$after = $model->data[$model->name][$col] = $this->_convert($before, $rule);
		}
		elseif(isset($model->data[$col])){
			$before = $model->data[$col];
			$after = $model->data[$col] = $this->_convert($model->data[$col], $rule);
		}
	}
	function _convert($v, $rule){
		if($v == '') {
			return $v;
		}
		switch($rule){
			case 'alphaNumeric':
			case 'email':
			case 'date':
			case 'email':
			case 'ip':
			case 'numeric':
			case 'url':
			case 'time':
			case 'valid_single':
			case 'valid_email':
			case 'valid_emailMulti':
			case 'valid_min_max':
				// 1バイト文字
				$v = mb_convert_kana($v, 'ras');
				break;
		   
			case 'valid_zen':
				// 全角文字
				$v = mb_convert_kana($v, 'ASKV');
				break;
	   
			case 'valid_kana':
				// 全角カタカナ文字
				$v = mb_convert_kana($v, 'KVC');
				break;
			   
			case 'valid_hirakana':
				// 全角ひらかな文字
				$v = mb_convert_kana($v, 'HVc');
				break;
			case 'valid_phone':
				$v = mb_convert_kana($v, 'ras');
				$v = str_replace(array('ー','―','‐'), '-', $v);
				break;
			case 'valid_zip':
				$v = mb_convert_kana($v, 'ras');
				$v = str_replace(array('ー','―','‐'), '-', $v);
				if(strlen($v) == 7 && preg_match("/^[0-9]+$/", $v)){
					$v = substr($v,0,3) . '-' . substr($v,3);
				}
				break;
			case 'valid_ymd':
				$v = mb_convert_kana($v, 'ras');
				$v = str_replace('/', '-', $v);
				break;
		}
		return $v;
	}
   
	#########################################################################
	/**
	 * 必須項目の出力文字列設定
	 */
	#########################################################################
	var $require_string = '';
	function setRequireString(&$model, $str) {
		$this->require_string = $str;
	}
   
	#########################################################################
	/**
	 * 必須項目の場合は設定文字列を返す
	 */
	#########################################################################
	function getRequireString(&$model, $col) {
		// バリデーション定義の読み込み
		if (method_exists($model, 'loadValidate') && !$this->loaded){
			$model->loadValidate();
			$this->loaded = TRUE;
		}
		if(!isset($model->validate[$col])) return '';
		if($this->_getArrayValueRecursive('required', $model->validate[$col])){
			return $this->require_string;
		}
		return '';
	}
   
	#########################################################################
	/**
	 * 配列にキーが存在していればその値を返す
	 */
	#########################################################################
	function _getArrayValueRecursive($strKey, $arrArray) {
		$ret = false;
		while ( list($key, $value) = each($arrArray)) {
			$ret = $key === $strKey ? $value : false;
			if (is_array($value) && ! $ret) {
				$ret = $this->_getArrayValueRecursive($strKey, $value);
			}
			if ($ret) break;
		}
		return $ret;
	}
   
	#########################################################################
	/**
	 * バリデーションの実行前に初期化を行う
	 */
	#########################################################################
	function beforeValidate(&$model, $options = NULL) {
		// バリデーション定義の読み込み
		if (method_exists($model, 'loadValidate') && !$this->loaded){
			$model->loadValidate();
			$this->loaded = TRUE;
		}
	   
		// 整形処理実行
		if($this->autoConvert){
			foreach($this->convert as $i => $arr){
				list($col, $rule) = each($arr);
				$this->convertData($model, $col, $rule);
			}
		}
		return TRUE;
	}
   
	#########################################################################
	/**
	 * バリデーション配列を引数の共通項のみとする
	 */
	#########################################################################
	function intersectValidate(&$model, $arg) {
		if (method_exists($model, 'loadValidate')){
			$model->loadValidate();
			$this->loaded = TRUE;
		}
		if(is_scalar($arg)){
			// for 'colA,colB'
			$okVali = array_flip(explode(',', $arg));
		}else{
			if(isset($arg[$model->name])){
				// for normal $data[model][colA]="xxx"
				$okVali = $arg[$model->name];
			}else{
				$cnt = Set::countDim($arg);
				// for saveAll $data[23][colA]="xxx"
				if($cnt == 2){
					$okVali = array_shift($arg);
				}else{
					list($col1, $col2) = each($arg);
					if(is_integer($col1)){
						// for columnArray array('colA', 'colB')
						$okVali = array_flip($arg);
					}else{
						// for columnKeyArray array('colA'=>"xxx", 'colB'=>"yyy")
						$okVali = $arg;
					}
				}
			}
		}
		$model->validate = array_intersect_key($model->validate, $okVali);
	}
   
	#########################################################################
	/**
	 * バリデーションの展開
	 */
	#########################################################################
	function setValidate(&$model, $arr) {
		foreach($arr as $col => $validate){
			// 通常のvalidate
			if(is_array($validate)){
				continue;
			}
			$validate = str_replace(" ", "", $validate);
			$validate = trim($validate, '|');
			$vali_arr = explode('|', $validate);
			// 必須項目判定
			if(in_array('required', $vali_arr)){
				$required   = TRUE;
				// 必須メッセージは優先表示（最後に再設定）
				$tmp = array_flip($vali_arr);
				unset($tmp['required']);
				$vali_arr = array_flip($tmp);
				$vali_arr[] = 'required';
			}else{
				$required   = FALSE;
			}
			$vali_arr = array_values($vali_arr);
			
			$allowEmpty = !$required;
			
			$allowEmptyExists = array_map( 'strpos', $vali_arr , array_fill(0,count($vali_arr),'allowEmpty') );
			if( FALSE !== ($key = array_search( 0 , $allowEmptyExists , TRUE )) ){
				$allowEmpty = TRUE;
				if (preg_match("/(.*?)\[(.*?)\]/", $vali_arr[$key], $match)){
					$allowEmpty  = low($match[2]) === 'false'? FALSE: (bool)$match[2];
				}
				unset($vali_arr[$key]);
			}
			
			$on = null;
			if(in_array('onCreate', $vali_arr)){
				$on = 'create';
				unset($vali_arr['onCreate']);
			}
			
			if(in_array('onUpdate', $vali_arr)){
				$on = 'update';
				unset($vali_arr['onUpdate']);
			}
			
			foreach($vali_arr as $rule){
				$param = "";
				if (preg_match("/(.*?)\[(.*?)\]/", $rule, $match)){
					$rule   = $match[1];
					$param  = $match[2];
				}
				
				if (method_exists($this, 'valid_' . $rule)){
					$rule = 'valid_' . $rule;
				}
				$msg  = '';
				if(isset($this->validateMessage[$rule])){
					$msg = $this->validateMessage[$rule];
				}else{
					$msg = $rule;
				}
				if($param && strstr($msg, '%s')){
					$tmp_p = array();
					foreach(explode(',', $param) as $_p){
						$tmp_p[] = trim($_p);
					}
					$msg = vsprintf($msg, $tmp_p);
				}
			   
			   
				$my_rule = $rule;
				if($param != ''){
					$my_rule = array($rule);
					foreach(explode(',', $param) as $p){
						$my_rule[] = trim($p);
					}
				}
				// var_dump(compact('col','rule','my_rule','required','allowEmpty','msg'));
				@$model->validate[$col][$rule] = array(
					'rule'       => $my_rule,
					'message'    => $msg,
					'required'   => $required,
					'allowEmpty' => $allowEmpty,
					'on'         => $on,
				);
				
				// 整形セット
				$this->SetConvert($model, $col, $rule);
			}
		}
		$this->loaded = TRUE;
	}
   
	#########################################################################
	/**
	 * メッセージのカスタマイズ
	 */
	#########################################################################
	function setMessage(&$model, $col, $rule, $message) {
		if (method_exists($this, 'valid_' . $rule)){
			$rule = 'valid_' . $rule;
		}
		if(isset($model->validate[$col]) && isset($model->validate[$col][$rule])){
			$model->validate[$col][$rule]['message'] = $message;
		}
	}
   
	#########################################################################
	/**
	 * バリデーションのクリア
	 */
	#########################################################################
	function clearValidate(&$model) {
		$this->loaded = TRUE;
		$model->validate = array();
		$this->convert = array();
	}
   
	#########################################################################
	/**
	 * 必須項目チェック
	 */
	#########################################################################
	function valid_required(&$model, &$data) {
		list($k, $v) = each($data);
		
		// 配列の場合(チェックボックス用)
		if(is_array($v)){
			foreach($v as $arr_v){
				if($arr_v){
					return TRUE;
				}
			}
			return FALSE;
		}
		
		if($v === ''){
			return FALSE;
		}else{
			return TRUE;
		}
	}
   
	#########################################################################
	/**
	 * 最大文字数チェック
	 */
	#########################################################################
	function valid_maxLen(&$model, &$data, $len) {
		list($k, $v) = each($data);
		if(mb_strlen($v)> $len){
			return FALSE;
		}else{
			return TRUE;
		}
	}
   
	#########################################################################
	/**
	 * 最少文字数チェック
	 */
	#########################################################################
	function valid_minLen(&$model, &$data, $len) {
		list($k, $v) = each($data);
		if(mb_strlen($v) <$len){
			return FALSE;
		}else{
			return TRUE;
		}
	}
	#########################################################################
	/**
	 * 文字数一致チェック
	 */
	#########################################################################
	function valid_equalLen(&$model, &$data, $len) {
		list($k, $v) = each($data);
		if(mb_strlen($v) != $len){
			return FALSE;
		}else{
			return TRUE;
		}
	}
   
	#########################################################################
	/**
	 * 電話番号チェック
	 */
	#########################################################################
	function valid_phone(&$model, &$data) {
		list($k, $v) = each($data);
		if($v === '') return TRUE;
	   
		if (preg_match("/^\d{2,5}\-\d{1,4}\-\d{1,4}$/", $v)) {
			return TRUE;
		}else{
			return FALSE;
		}
	}
   
	#########################################################################
	/**
	 * 郵便番号チェック
	 */
	#########################################################################
	function valid_zip(&$model, &$data) {
		list($k, $v) = each($data);
		if($v === '') return TRUE;
	   
		if (preg_match("/^\d{3}\-\d{4}$/", $v)) {
			return TRUE;
		}else{
			return FALSE;
		}
	}
   
	#########################################################################
	/**
	 * 全角チェック
	 */
	#########################################################################
	function valid_zen(&$model, &$data) {
		list($k, $v) = each($data);
		if($v === '') return TRUE;
		$v = mb_convert_encoding($v, 'UTF-8');
		if (!preg_match("/(?:\xEF\xBD[\xA1-\xBF]|\xEF\xBE[\x80-\x9F])|[\x20-\x7E]/", $v)) {
			return TRUE;
		}
		return FALSE;
	}
   
	#########################################################################
	/**
	 * カタカナチェック
	 */
	#########################################################################
	function valid_kana(&$model, &$data) {
		list($k, $v) = each($data);
		if($v === '') return TRUE;
	   
		$v = mb_convert_encoding($v, 'UTF-8');
		if (preg_match("/^(?:\xE3\x82[\xA1-\xBF]|\xE3\x83[\x80-\xB6]|ー)+$/", $v)) {
			return TRUE;
		}
		return FALSE;
	}
   
	#########################################################################
	/**
	 * ひらかなチェック
	 */
	#########################################################################
	function valid_hirakana(&$model, &$data) {
		list($k, $v) = each($data);
		if($v === '') return TRUE;
		$v = mb_convert_encoding($v, 'UTF-8');
		if (preg_match("/^(?:\xE3\x81[\x81-\xBF]|\xE3\x82[\x80-\x93])+$/", $v)) {
			return TRUE;
		}
		return FALSE;
	}
   
	#########################################################################
	/**
	 * 環境依存文字・旧漢字などJISに変換できない文字チェック
	 */
	#########################################################################
	function valid_jis(&$model, &$data) {
		list($k, $v) = each($data);
		if($v === '') return TRUE;
		$myEnc = Configure::read('App.encoding');
		// 対象外
		$v = str_replace(array('～', 'ー', '－', '∥', '￠', '￡', '￢'), "", $v);
		$v2 = mb_convert_encoding($v, 'iso-2022-jp', $myEnc);
		$v2 = mb_convert_encoding($v2, $myEnc,'iso-2022-jp');
		if ($v == $v2) {
			return TRUE;
		}
		return FALSE;
	}
   
	#########################################################################
	/**
	 *  1バイト文字列チェック
	 */
	#########################################################################
	function valid_single(&$model, &$data) {
		list($k, $v) = each($data);
		if($v === '') return TRUE;
	   
		if(strlen($v) != mb_strlen($v)){
			return FALSE;
		}
		return TRUE;
	}
	   
	#########################################################################
	/**
	 *  確認入力用
	 */
	#########################################################################
	function valid_confirm(&$model, &$data, $col ) {
		list($k, $v) = each($data);
		if(!isset($model->data[$model->name][$col])){
			return FALSE;
		}
		if($v === $model->data[$model->name][$col]){
			return TRUE;
		}
		return FALSE;
	}
   
	#########################################################################
	/**
	 *  メールアドレス妥当性チェック
	 */
	#########################################################################
	function valid_email(&$model, &$data ) {
		list($k, $v) = each($data);
		if($v === '') return TRUE;
	   
		$__pattern = '(?:[a-z0-9][-a-z0-9]*\.)*(?:[a-z0-9][-a-z0-9]{0,62})\.(?:(?:[a-z]{2}\.)?[a-z]{2,4}|museum|travel)';
		$__regex   = '/^[a-z0-9!#$%&\'*+\/=?^_`{|}~-]+(?:\.[a-z0-9!#$%&\'*+\/=?^_`{|}~-]+)*@' . $__pattern . '$/i';
	   
		if (preg_match($__regex, $v)) {
			return true;
		} else {
			return false;
		}
	}
   
	#########################################################################
	/**
	 *  メールアドレス妥当性チェック(複数カンマ区切り)
	 */
	#########################################################################
	function valid_emailMulti(&$model, &$data ) {
		list($k, $v) = each($data);
		if($v === '') return TRUE;
	   
		$mails = explode(',', $v);
		foreach($mails as $m){
			$myData = array($k=>$m);
			if(!$this->valid_email($model, $myData)){
				return FALSE;
			}
		}
		return TRUE;
	}
   
	#########################################################################
	/**
	 *  YYYY-MM-DD形式かどうか
	 */
	#########################################################################
	function valid_ymd(&$model, &$data, $col ) {
		list($k, $v) = each($data);
		if($v === '') return TRUE;
	   
		$tmp = explode('-', $v);
		if(count($tmp) != 3) return false;
		$yyyy = $tmp[0];
		$mm = $tmp[1];
		$dd = $tmp[2];
		return checkdate($mm, $dd, $yyyy);
	}
	
	function valid_min_max(&$model, &$data , $min , $max){
		list($k, $v) = each($data);
		if($v === '') return TRUE;
		
		if (!is_numeric($v)) {
			return FALSE;
		}
		
		return $min <= $v && $v <= $max;
	}
}