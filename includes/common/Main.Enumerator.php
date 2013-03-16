<?php
  
  if (@preg_match("includes/i", $_SERVER['PHP_SELF'])) 
    die ("You can't access this file directly...");

	include_once("includes/DatabaseClasses/Parts.Entities.php");

  /**
   * class Enumerator
   *
   * The class represents the common enumerations of the system. 
   * PHP doesn't have the static enums so we use the Methods and Arrays
   *
   * @package Common.pkg
   */

	class Enumerator
	{		
		public static function Entity($identifier)
		{
			$ent = new Entities(false);
		
			switch($identifier)
			{
				case 1:
				case 'USR':
				
					$ent->id = 1;
					$ent->guid = '7820cf81-2719-11e0-b8bb-68b55483945b';
					$ent->name = 'Users';
					$ent->code = 'USR';
				
				break;
					
				case 2: 
				case 'MAT':
				
					$ent->id = 2;
					$ent->guid = '7820d115-2719-11e0-b8bb-68b55483945b';
					$ent->name = 'Materials';
					$ent->code = 'MAT';
					
				break;
				
				case 3:
				case 'MGR':
			
					$ent->id = 3;
					$ent->guid = '7820d47d-2719-11e0-b8bb-68b55483945b';
					$ent->name = 'Materials Groups';
					$ent->code = 'MGR';
					
				break;
			
				case 4:
				case 'PRJ':
				
					$ent->id = 4;
					$ent->guid = '7820d623-2719-11e0-b8bb-68b55483945b';
					$ent->name = 'Projects';
					$ent->code = 'PRJ';
					
				break;
				
				case 5:
				case 'STP':
			
					$ent->id = 5;
					$ent->guid = '7820d79b-2719-11e0-b8bb-68b55483945b';
					$ent->name = 'Projects Steps';
					$ent->code = 'STP';
					
				break;

				case 6:
				case 'COMP':

					$ent->id = 6;
					$ent->guid = '7820d924-2719-11e0-b8bb-68b55483945b';
					$ent->name = 'Companies';
					$ent->code = 'COMP';
					
				break;
			
				case 7:
				case 'CGR':
				
					$ent->id = 7;
					$ent->guid = '7820da57-2719-11e0-b8bb-68b55483945b';
					$ent->name = 'Companies Groups';
					$ent->code = 'CGR';
					
				break;

				case 8:
				case 'CNTR':			

					$ent->id = 8;
					$ent->guid = '7820db93-2719-11e0-b8bb-68b55483945b';
					$ent->name = 'Countries';
					$ent->code = 'CNTR';
					
				break;
			
				case 9:
				case 'CTY':

					$ent->id = 9;
					$ent->guid = '7820dccd-2719-11e0-b8bb-68b55483945b';
					$ent->name = 'City';
					$ent->code = 'CTY';

				break;
			
				case 10:
				case 'ADDR':
				
					$ent->id = 10;
					$ent->guid = '7820de0b-2719-11e0-b8bb-68b55483945b';
					$ent->name = 'Address';
					$ent->code = 'ADDR';

				break;
				
				case 11:
				case 'NEWS':
				
					$ent->id = 11;
					$ent->guid = '4dd9d187-3554-11e0-8c36-c88a591e8c64';
					$ent->name = 'News';
					$ent->code = 'NEWS';
				
				break;
				
				case 12:
				case 'BAN':
				
					$ent->id = 12;
					$ent->guid = '370db75e-943e-11e0-b461-608ea592ece7';
					$ent->name = 'Banners';
					$ent->code = 'BAN';
				
				break;
			}
			

			
			return $ent;
		}
	
		public static function Entities()
		{
			$result = array();

			$result[1] =  self::Entity(1);
			$result[2] =  self::Entity(2);
			$result[3] =  self::Entity(3);
			$result[4] =  self::Entity(4);
			$result[5] =  self::Entity(5);
			$result[6] =  self::Entity(6);
			$result[7] =  self::Entity(7);
			$result[8] =  self::Entity(8);
			$result[9] =  self::Entity(9);
			$result[10] = self::Entity(10);			
			
			return $result;
		}
		
		public static $browser = array ("fox" => "Firefox", "ia" => "MSIE", "o" => "Opera", "apl" => "Safari", "chr" => "Chrome");
		
		/**
		 * Тип контенту
		 */
		public static function ContentTypes($type)
		{
			$result = "";
			
			switch($type)
			{
				case "text/html":
				case TEXT:
				case HTML:
					$result = "text/html";
				break;
				
				case "application/json":
				case JSON:
					$result = "application/json";
				break;
			
				case "image/jpeg":
				case JPEG:
					$result = "image/jpeg";
				break;
				
				case "image/gif":
				case GIF:
					$result = "image/gif";
				break;
				
				case "image/png":
				case PNG:
					$result = "image/png";
				break;
				
			}
			
			return $result;
		}
	}
?>