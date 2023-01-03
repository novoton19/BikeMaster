<?php
	#Making sure that this script is running as module
	if (!count(debug_backtrace()))
	{
		return;
	}


	abstract class GeneralFunctions
	{
		static function getValue($array = [], $key = '', $defaultValue = null, $mustBeSameType = true)
		{
			#Checking if value exists
			if (!isset($array[$key]))
			{
				return $defaultValue;
			}
			#Getting value
			$value = $array[$key];
			#Checkking if exists
			if (is_null($value) and is_null($defaultValue))
			{
				return $defaultValue;
			}
			#Checking type
			if ($mustBeSameType and (!is_null($defaultValue)) and gettype($value) != gettype($defaultValue))
			{
				#Not same type
				return $defaultValue;
			}
			return $value;
		}
		static function toBoolean($value)
		{
			if (strtolower($value) == 'false')
			{
				return false;
			}
			return boolval($value);
		}
		#https://www.delftstack.com/howto/php/php-uuid/
		static function generateUUID()
		{
			return sprintf(
				'%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
				#32 bits for the time_low
				mt_rand(0, 0xffff), mt_rand(0, 0xffff),
				#16 bits for the time_mid
				mt_rand(0, 0xffff),
				#16 bits for the time_hi,
				mt_rand(0, 0x0fff) | 0x4000,
				#8 bits and 16 bits for the clk_seq_hi_res,
				#8 bits for the clk_seq_low,
				mt_rand(0, 0x3fff) | 0x8000,
				#48 bits for the node
				mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
			);
		}
		#https://stackoverflow.com/questions/4356289/php-random-string-generator
		static function generateRandomString($length = 32)
		{
			#Available characters
			$characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789@#$%^&<>{}[]()_-+~*,.?!\\|/"\'';
			#Length of the available characters
			$charactersLength = strlen($characters);
			#Resulting string
			$result = '';
			#Generating string
			for ($i = 0; $i < $length; $i++)
			{
				#Adding a character
				$result .= $characters[
					rand(0, $charactersLength - 1)
				];
			}
			return $result;
		}
		#Validates date
		#https://www.php.net/manual/en/function.checkdate.php
		static function validateDate($date, $format = 'Y-m-d')
		{
			$d = DateTime::createFromFormat($format, $date);
    		return $d && $d->format($format) == $date;
		}
	}
?>