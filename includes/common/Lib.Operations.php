<?php

  if (@preg_match("includes/i", $_SERVER['PHP_SELF'])) 
    die ("You can't access this file directly...");

	/** 
	 *	Library is the package with the common library functionality
	 *
	 *  @package Library.pkg
	 */


	/** 
	 *	Class Operations
	 *
	 *  Class implements the common functions for operating with the strings 
	 *
	 *  @package Library.pkg
	 */

	
	class Operations 
	{	
	
		public static function StripWhitespace($string, $replacement = " ")
		{
			return preg_replace("/\s+/", $replacement, $string);
		}
		
		public static function TSymbol($symbol)
		{
			$case = false; // lowcase

			if(strtoupper($symbol) == $symbol)
			{
				$case = true; // upper
			}
			
			//$symbol = strtolower($symbol);
			
			switch($symbol)
			{
				case "А":
				case "а":
					$res = "a";
				break;
		
				case "Б":
				case "б":
					$res = "b";
				break;
		
				case "В":
				case "в":
					$res = "v";
				break;
		
				case "Г":
				case "г":
					$res = "g";
				break;
				
				case "Д":
				case "д":
					$res = "d";
				break;
						
				case "Е":
				case "е":
					$res = "e";
				break;
				
				case "Ё":
				case "ё":
					$res = "yo";
				break;
				
				case "Ж":
				case "ж":
					$res = "zh";
				break;
				
				case "З":
				case "з":
					$res = "z";
				break;
				
				case "И":
				case "и":
					$res = "i";
				break;
				
				case "Ї":
				case "ї":
					$res = "yi";
				break;
				
				case "Й":
				case "й":
					$res = "y";
				break;
				
				case "К":
				case "к":
					$res = "k";
				break;
				
				case "Л":
				case "л":
					$res = "l";
				break;
				
				case "М":
				case "м":
					$res = "m";
				break;
				
				case "Н":
				case "н":
					$res = "n";
				break;
				
				case "О":
				case "о":
					$res = "o";
				break;
				
				case "П":
				case "п":
					$res = "p";
				break;
				
				case "Р":
				case "р":
					$res = "r";
				break;
				
				case "C":
				case "с":
					$res = "s";
				break;
				
				case "Т":
				case "т":
					$res = "t";
				break;
		
				case "У":
				case "у":
					$res = "u";
				break;
				
				case "Ф":
				case "ф":
					$res = "f";
				break;
				
				case "Х":
				case "х":
					$res = "h";
				break;
				
				case "Ц":
				case "ц":
					$res = "ts";
				break;

				case "Ч":
				case "ч":
					$res = "ch";
				break;
				
				case "Ш":
				case "ш":
					$res = "sh";
				break;
				
				case "Щ":
				case "щ":
					$res = "sh";
				break;
				
				case "Ъ":
				case "ъ":
					$res = "";
				break;
				
				case "Ы":
				case "ы":
					$res = "i";
				break;
				
				case "ь":
				case "ь":
					$res = "`";
				break;
				
				case "Э":
				case "э":
					$res = "e";
				break;
				
				case "Ю":
				case "ю":
					$res = "yu";
				break;
				
				case "Я":
				case "я":
					$res = "ya";
				break;
				
				case " ":
					$res = "_";
				break;
				
				default: 
					$res = $symbol;
				break;
			}
			
			/*
			if($case)
			{
				$res = mb_strtoupper($res);
			}*/
			
			return $res;
		}
	
		public static function Translator($string, $whitespace = " ")
		{
			$result = self::StripWhitespace($string); 
			return preg_replace("/(.{1})/eu", "self::TSymbol('\\1')", $result);
		}
	}

?>