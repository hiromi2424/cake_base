<?php
if(Configure::read() == 0)
	return '';

if($session->check('debug.printable')){
	// var_dump($session->read('debug.printable'));
	foreach($session->read('debug.printable') as $_number => $_debug):
		$_cssId = "__debug.trace_{$_number}";
		$_jsElement = "document.getElementById('{$_cssId}')";
		$_currentTrace = array_shift($_debug['trace']);
		$_title = "";
		// $_title = ife(@$_currentTrace['class'] , @$_currentTrace['class'].'::' , '');
		$_title .= $_currentTrace['function'] . '()';
		$_title .= ' - ';
		$_title .= Debugger::trimPath($_currentTrace['file']);
		$_title .= ' line ' . $_currentTrace['line'];
		echo $html->link($_title,'javascript:void(0)',aa('onclick',"{$_jsElement}.style.display = {$_jsElement}.style.display == 'none'?'':'none'; ",'escape',false));
		
		echo '<br />';
		?>
		<div id="<?php echo $_cssId ?>" style="display: none">
		<?php
		foreach($_debug['trace'] as $_trace){
			echo ife(@$_trace['class'] , @$_trace['class'].'::' , '');
			echo $_trace['function'] . '() - ' . Debugger::trimPath($_trace['file']) . ', line ' . $_trace['line'];
			echo '<br />';
		}
		?>
		</div>
		
		<?php
		// ("%s line %s",$_debug['file'],$_debug['line']);
		
		foreach($_debug['args'] as $key => $_arg):
			$_cssId = "__debug.print_{$key}_{$_number}";
			$_jsElement = "document.getElementById('{$_cssId}')";
			$_jsToggle = "document.getElementById('{$_cssId}toggle')";
			echo $html->link(
				'-',
				'javascript:void(0)',
				array(
					'onclick' => str_replace(array("\n",'\t'),' ',"
						if({$_jsElement}.style.display == 'none'){
							{$_jsElement}.style.display = '';
							{$_jsToggle}.innerHTML = '-';
						}else{
							{$_jsElement}.style.display ='none';
							{$_jsToggle}.innerHTML = '+';
						}
					"
					),
					'escape' => false,
					'id' => $_cssId.'toggle',
					'style' => 'border:1px solid #033; text-decoration:none ',
				)
			);
			
			?>
			<pre id="<?php echo $_cssId ?>">
			<?php var_dump($_arg) ?>
			</pre>
			<?php
		endforeach;
	endforeach;
	$session->delete('debug.printable');
}

?>