<?php
	
  if (@preg_match("/includes/i", $_SERVER['PHP_SELF'])) 
    die ("You can't access this file directly...");

	include_once("includes/common/Tag/Lib.Button.php");
	include_once("includes/common/Tag/Lib.Hidden.php");

/**
 * class Form
 *
 * Класс, що необхідно.
 *
 * The main CommonPage
 * 
 * @package Pages.pkg
 */


class Form extends Tag
{
	public $tagName = "form";
 	
 	public $isBlockType = false;
 	
 	public $name = "";
 	
 	/**
 	 * Одержує атрибути, що будуть відрисовані підчас відчинення тагу
 	 */
 	public $tagAttributes = array ("action" => "", "method" => "post");
 	
	/**
	* Головний конструктор 
	*
	* Main constructor
	*/
	public function __construct() 
	{
	}
 
 	public function RenderTop() 
 	{
 		if(strlen($this->name) > 0)
 		{
 			$this->tagAttributes["name"] = $this->name;
 		}
 		
 		if(strlen($this->id) > 0)
 		{
 			$this->tagAttributes["id"] = $this->id;
 		}
 	
 		$formsText = $this->OpenTag();
 		 		
 		$hdn = new Hidden();
 		$hdn->SetId("__SVar");
		$hdn->SetName("__SVar");
		$hdn->SetValue(Session::$mainSessionVariable);
		
		$formsText .= "\r\n".$hdn->OpenTag();
		
		return $formsText;
 	}
 	
 	public function RenderSubmitButton($valueText) 
 	{
 		$sbt = new Button();
 		$sbt->value = $sbt->title = $valueText;
 		
 		return $sbt->OpenTag();
 	}
 	
 	public function RenderBottom ()
 	{
 		return $this->CloseTag();		 		
 	}
 	
 	/**
 	 * Рисує зірочку для полів форми
 	 */
 	public function RenderAsterisks($alertText, $isValidForm = false, $isVisible = false)
 	{ 
 		return ( ( ((strlen($alertText) > 0) | $isVisible) && !$isValidForm) ? "<font style=\"color:#FF0000\" title=\"".$alertText."\"><b>*</b></font>" : "");
 	}
}

?>