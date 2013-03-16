<?php

  if (@preg_match("/includes/i", $_SERVER['PHP_SELF'])) 
    die ("You can't access this file directly...");
    
//Error_Reporting(E_ALL & ~E_NOTICE);

/**
 * The Package security classes etc
 *
 * @package Security.pkg
 */
 
/**
 * class Security
 *
 * The class holds the common security methods and operations. Almost statically
 * 
 * @package Security.pkg
 */

	class Security
	{
		//// !!! CONFIGURATIONS SETTINGS RELATED SECURITY AND NOT !!!
		const domain = "myxata.com"; //should be set after installation
		const version = 0.1;
		const MaxErrorCount = 7;
		const MercSignature = "";
		
		private static $hashType = "sha1";  // sha1 or md5
		
		public static $modulesSet = array(  // перелік дозволенних сторінок
									"Main" 	// головна сторінка
										=> array(),
										
									"Image" // Блок динамічних зображень
										=> array(),
										
									"File" // Блок завантаження файлу
										=> array(),
										
									"Test" 
										=> array ("Testpage" => true), // Спеціальна локальна тестова сторінка
										
									"User"  // модуль користувача
										=> array ( 					// перелік сторінок, що відносяться до користувача
											"Profile"	   => true, // профіль користвувача
											"Registration" => true, // реєстрація користувача
											"Company"	   => true, // додавання нової компанії
											"Friends"	   => true, // друзі користувача
											"Materials"	   => true, // матеріали користувача
											"News"		   => true  // новини
											),
											
									"Project" // модуль проектів
										=> array (
											"Project" 	   => true // головна сторінка додавання та редагування проекту
										),
										
									"Companies" // модуль компаній
										=> array (
											"Company" => true // главная страница компаний
										),

									"Materials" // додати матеріали
										=> array
										   ( 
											"Material" => true  // редагування матеріалів
										   ),
										   
									"Contact" // форма контакту
										=> array
											(
												"Contact" => true // форма зворотнього зв'язку
											),
											
									"Services"
										=> array
											(
												"Services" => true // форма надання послуг
											),
											
									"Donate" // форма подяки
										=> array
											(
												"Donate" => true  // сторінка оброки подяки
											),
											
									'Terms'
										=> array
											(
												"Terms" => true
											),
											
									"Privacy"
										=> array
											(
												"Privacy" => true
											),
											
									"News"
										=> array
											(
												"News" => true
											),

									"About" 
										=> array
											(
												"About" => true // 
											),
										
									"Actions" // спеціальний модуль обробки AJAX запитів
									

									);
		
		/**
		 * Повертає першу сторінку для вказанного модуля
		 */							
		public static function GetDefaultPageForModule($module)
		{
			return array_search(true, self::$modulesSet[$module] );
		}

		/**
		 * Перевіряє чи дозволено ту сторінку, що прийшла у запиті та повертає її значення із масиву $modulesSet
		 */
		public static function PageIsAllowed($module, $page = "")
		{
			$module2 = ucfirst(strtolower($module));
			$page2 = ucfirst(strtolower($page));

			// в майбутньому тут буде перевірка також прав користувача на перегляд сторінки
			$result = "";

			if( $module2 == "Image" ) // just the image file id
			{
				if(!is_numeric($page))
				{
					$result = self::DecodeUrlData($page);
				}
				else
				{
					$result = (float)$page;
				}
			}
			if( $page == "" || strlen($page) > 50 )
			{
				$result = Security::GetDefaultPageForModule($module2);
			}
			elseif( @array_key_exists($page2, self::$modulesSet[$module2]) && self::$modulesSet[$module2][$page2] )
			{
				$result = $page2;
			}

			return $result;
		}
		
		/**
		 * Повертає хеш строки за загальним алгоритмом, чи за запитом
		 */
		public static function CommonHash($var, $hashType = "")
		{
			$result = "";
			
			if($hashType == "")
			{
				$hashType = self::$hashType;
			}
			
			switch($hashType)
			{
				case "md5":
					$result = md5($var);
				break;
					
				case "sha1":
				default :
					$result = sha1($var);
				break;
			}
			
			return $result;
		}
		
		/**
		 * Очищує строку від потенційної небезпеки використання спеціальних символів 
		 */
		public static function StripPostVar($secvalue)
		{
			$secvalue = strip_tags($secvalue);
			$secvalue = htmlspecialchars($secvalue);
			$secvalue = nl2br($secvalue);
			
			return $secvalue;
		}
		
		public static function CreateSessionVar($login)
		{
			$SVAR = self::CommonHash($login."_".$_SERVER['REMOTE_ADDR']."_".$_SERVER['HTTP_USER_AGENT']."_".microtime(true));
			
			return $SVAR;
		}
		
		/**
		 * Шифрує данні задля передачі у запиті
		 */
		public static function EncodeUrlData($data /*could be as array as a string*/)
		{ 
			//!!! COULD BE INCREASED ENCRYPTION LEVEL IF THIS NEEDED
			return base64_encode(serialize($data));
		}
		
		public static function DecodeUrlData($data)
		{
			$result = "";
			
			try 
			{
				$result = unserialize(base64_decode($data));
			}
			catch(Exception $ex)
			{
			}
			
			return $result;
		}
	}

?>