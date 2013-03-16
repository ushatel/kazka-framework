<?php
  if (@preg_match("/includes/i", $_SERVER['PHP_SELF'])) 
    die ("You can't access this file directly...");


/** 
 *	Library is the package with the common library functionality
 *
 *  @package Library.pkg
 */

/**
 * class Tag
 *
 * Цей клас реалізує операції та відрисовку будь якого тегу <tag>
 *
 * The class implements the common renderings and operations for the any <tag> 
 * 
 * @package Library.pkg
 */
 
 class Tag 
 {
 	public $tagName = "tagName";
 	
 	public $isBlockType = true;
 	
 	public $id = "tagId";
 	public $name = "tagName";
 	
 	public $style = "";
 	
 	/**
 	 * Одержує атрибути, що будуть відрисовані підчас відчинення тагу
 	 */
 	public $tagAttributes = array ("name" => "value");
 
 	/**
 	 * Відкриває таг, із атрибутами
 	 */
 	function OpenTag() 
 	{
 		$this->tagAttributes = array_merge($this->tagAttributes/*, 
						 	   array("name" => $this->name, "id" => $this->id)*/ );
 		
 		return "<".$this->tagName.$this->TagAttributes();
 	}
 	
 	/** 
  	 * Повертає атрибути
 	 */
 	private function TagAttributes() 
 	{
 		$attribText = "";
 		foreach($this->tagAttributes as $attribName => $attribValue) 
 		{
 			$attribText .= " ".$attribName."=\"".$attribValue."\"";
 		}
 		return $attribText.$this->CloseAttributes();
 	}
 	 	
 	/**
 	 * Зачиняє заголовок тагу
 	 */
 	private function CloseAttributes() 
 	{
 		if($this->isBlockType)
 			return " />";
 		else 
 			return " >";
 	}
 	
 	/**
 	 * Відрисовує атрибут стилю
 	 */
 	protected function SetStyleAttribute($name, $value, $assign = 0)
 	{
 		$result = "";
 		if(strlen($value) > 0)
 		{
	 		$result .= $name.":".$value.";";
	 	}

		if($assign == 0)
		{
			$this->style .= $result;
		}
		elseif($assign == 1)
		{
			$result = "style='".$result."'";
		}
		elseif($assign == 2)
		{
			//$result = $result; Повертаємо простий результат без атрибуту style
		}

 		return $result;
 	}
 	
 	public function SetId($id)
 	{
 		$this->tagAttributes["id"] = (string)$id;
 	}
 	
 	/**
 	 * Зачиняє таг
 	 */
 	function CloseTag() 
 	{
 		if(!$this->isBlockType)
	 		return "</".$this->tagName.">";
 	}
 }

?>