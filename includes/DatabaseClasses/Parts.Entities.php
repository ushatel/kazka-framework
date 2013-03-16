<?php
	
  if (@preg_match("/includes/i", $_SERVER['PHP_SELF'])) 
    die ("You can't access this file directly...");

  include_once("includes/DatabaseClasses/Db.CommonClass.php");    
  include_once("includes/DatabaseClasses/Db.EntitiesTbl.php");

/**
 * The Package collects the entity related classes etc
 *
 * @package Entities.pkg
 */

/**
 * class Entities
 *
 * The main class of the Entities. Used for the operating with the entities 
 * 
 * @package Entities.pkg
 */
class Entities extends CommonClass
{	
	private $entity = NULL;

	public $id = 0;
	public $guid = "";
	public $name = "";
	public $code = "";
	
	function __construct($need_init = true)
	{
		if($need_init)
		{
			$this->entity = new EntitiesTbl();
		}
	}
	
	/**
	 * Повертає усі сущності
	 */
	public function LoadEntities()
	{
		$result = NULL;
		
		$result = $this->entity->LoadEntities();
		
		return $result;
	}
}

?>