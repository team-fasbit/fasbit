<?php
class Errors {
	public static $errors;
	
	function add($error) {
		if (!empty($error))
			self::$errors[] = $error;
	}
	
	function merge($error_array) {
		if (!is_array(self::$errors)) {
			self::$errors = $error_array;
		}
		if (is_array($error_array)) {
			self::$errors = array_merge(self::$errors,$error_array);
		}
	}
	
	function display() {
		if (!empty(self::$errors)) {
			echo '<ul class="errors">';
			foreach (self::$errors as $name => $error) {
				echo '<li><div class="error_icon"></div>'.ucfirst(str_ireplace('[field]',$name,$error)).'</li>';
			}
			echo '</ul><div class="clear">&nbsp;</div>';
		}
	}
}
?>