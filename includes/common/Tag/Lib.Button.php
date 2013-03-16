<?php
  if (@preg_match("includes/i", $_SERVER['PHP_SELF'])) 
    die ("You can't access this file directly...");

  include_once("Lib.Tag.php");

/** 
 *	Library is the package with the common library functionality
 *
 *  @package Library.pkg
 */

/**
 * class Form
 *
 * Цей клас реалізує операції та відрисовку тегу <form>
 *
 * The class implements the common renderings and operations with the <form> tag
 * 
 * @package Library.pkg
 */

class Button extends Tag 
{
 	public $tagName = "input";
 	public $isBlockType = true;
	public $isSimple = false;

 	public $tagAttributes = array("type" => "button");

 	public $id = "formsId";
 	public $name = "formsName";
 	public $title = "value text";
 	public $value = "value text";
 	
 	public $isSubmit = true;
 	public $isAJAX = false;
 	
 	/**
 	 * Open Tag into the tag
 	 */
 	public function OpenTag() 
 	{
		$this->tagAttributes["id"]    = $this->id;
		$this->tagAttributes["name"]  = $this->name;
		$this->tagAttributes["title"] = $this->title;
		
		if($this->isSubmit) 
		{
			$this->tagAttributes["onclick"] = "SButtonClick(this);";
		}
		elseif($this->isAJAX)
		{
			$this->tagAttributes["onclick"] = "SAJAXButtonClick(this);";
		}
		
		if($this->isSimple)
		{
			$this->tagAttributes["value"] = $this->value;
			
			$result = parent::OpenTag();
		}
		else
		{
			$this->tagName = "button";
			$result = parent::OpenTag().$this->value. parent::CloseTag();
		}
				
		
 		return $result;;
 	}
}

?>