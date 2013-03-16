<?php
  if (@preg_match("includes/i", $_SERVER['PHP_SELF'])) 
    die ("You can't access this file directly...");

  include_once("includes/common/Tag/Lib.Tag.php");

/** 
 *	Library is the package with the common library functionality
 *
 *  @package Library.pkg
 */

/**
 * class Anchor 
 *
 * Цей клас реалізує операції та відрисовку тегу anchor: <a>
 *
 * The class implements the common renderings and operations with the <a> tag
 * 
 * @package Library.pkg
 */
 
 class Anchor extends Tag
 {
 	public $tagName = "a";
 	
 	/**
 	 * Вказує, чи є це посилання звичайним, тобто чи для нього потрібен захист фреймворку.
 	 */
  	public $isTraditionalHref = true;
 	
 	public $SCode = "";
 	
 	public $isBlockType = false;

 	public $tagAttributes = NULL;

 	public $id = "anchorId";
 	public $title = "value text";
 	public $href = "value text";
 	public $target = "_self";
	public $class = "anchorClass";
 	
 	public $onClick = "";
 	public $onSuccessMethod = "";
 	
 	public $hrefAJAX = "";
 	
 	public $refreshElementId = "";
 	
 	public $appendClientScript = "";
 	public $applyScripts = false;
 	
 	public $getParamsValues = true;
 	
 	public $params = NULL;
 	 	 	
 	/**
 	 * Open Tag into the tag
 	 */
 	public function OpenTag() 
 	{
 		$result = "";
 	
 		if($this->id != "anchorId")
 		{
	 		$this->tagAttributes["id"]    = $this->id;
 		}
 		else
 		{
 			$this->tagAttributes["id"] 	  = $this->id = "id_".rand(1001, 9999);
 		}
 		
 		$this->tagAttributes["title"] 	= $this->title;
 		$this->tagAttributes["href"]  	= $this->href;
 		$this->tagAttributes["target"]	= $this->target;
		
		$this->tagAttributes["class"]	= $this->class;
 		
		// Обычная ссылка типа <a></a> или c AJAX
 		if(!$this->isTraditionalHref)
 		{ 		
 			$params_array = "";
 
 			if(is_array($this->params) && count($this->params) > 0)
 			{
 				$params_array = "lnk.params = {";
 				
 				$isFirst = true;

 				foreach($this->params as $key => $value)
 				{
 					if(!$isFirst)
 						$params_array .= ",";
 					else
 						$isFirst = false;
 						
	 				$params_array .= "".$key.":'".$value."'";
	 			}
 				
 				$params_array .= "};";
 			}
 			
 			$getParamsValues = "";
 			if(!$this->getParamsValues)
 			{
 				$getParamsValues = "lnk.getParamsValues = false; ";
 			}
 			
			$this->appendClientScript = "var lnk = new Link(); ". // put this in Array
							"lnk.sCode = '".$this->SCode."'; ".
							"lnk.refreshElementId = '".$this->refreshElementId."'; ".
							"lnk.href = '".$this->hrefAJAX."'; ".
							$getParamsValues.
							"lnk.listId = '".$this->id."'; "
							.$params_array.
							"Objects.Links['".$this->id."'] = lnk; ";
 			 			
 			if($this->applyScripts)
 			{
				$result = "<script>".$this->appendClientScript."</script>";
 			}

			if(strlen($this->onClick) == 0)
			{
				$this->onClick = "AjaxLinkClick(this); return false;"; 				
 			}
 			
 		}

		$this->tagAttributes["onclick"] = $this->onClick;

				
 		return $result.parent::OpenTag();
 	}
 }

?>