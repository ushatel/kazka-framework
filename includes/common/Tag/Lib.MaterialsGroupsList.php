<?php

  if (@preg_match("/includes/i", $_SERVER['PHP_SELF'])) 
    die ("You can't access this file directly...");

  include_once("Lib.Tag.php");

  include_once("includes/DatabaseClasses/Parts.Countries.php");
  include_once("includes/DatabaseClasses/Parts.Materials.php");

/** 
 *	Library is the package with the common library functionality
 *
 *  @package Library.pkg
 */

/**
 * class MaterialsGroupsList 
 *
 * Малюється комбокс із переліком груп матеріалів
 *
 * @package Library.pkg
 */

class MaterialsGroupsList extends Tag
{

 	public $tagName = "select";
 	
 	public $isBlockType = false;
 	
 	public $tagAttributes = NULL;
 	
 	public $name = "materials_groups_id";
	public $id = "materials_groups_id";
	public $width = "100%";
	public $display = "visible";
	
	public $showLinks = false;
	
	public $allowSelect = false;
 	 	
 	public $optionsArray = array( // options to draw
 				array("Title" => "", "Value" => "", "Id" => "", "Selected" => false)
 				);
 	
  	public function __construct() 
 	{
 	} 
 	
 	public function GetMaterialsGroupsList($selected = "")
 	{
 		$materialsGroups = new Materials();
 		$result = $materialsGroups->GetMaterialsGroupsList();
 		
 		$select = new Select();
		$select->tagAttributes = $this->tagAttributes;
 		$select->tagAttributes['name'] = $this->name;
		$select->tagAttributes['id'] = $this->id;
		
		if(strlen($this->width) > 0)
		{
			$select->tagAttributes['style'] .= "width:".$this->width;
		}
		
 		while($row = mysql_fetch_array($result))
 		{
 			$empty_group = "group_".$row["MATERIALS_GROUPS_ID"];
 			$groups_id = Security::EncodeUrlData($empty_group);

 			$opt = array("Title" => $row["GROUPS_NAME"], "Value" => $groups_id, "Id" => $groups_id, "Selected" => (((string)$selected == $groups_id || (string)$selected == $empty_group || $row["MATERIALS_GROUPS_ID"] == (float)$selected) ? true : false));
 			array_push($select->optionsArray, $opt);
 		}

 		return $select->RenderTop().$select->RenderBottom();
 	}	
	
	public static function ClientScripts($selectedId = 'parent_materials_groups_id')
	{
		$result = ' 
			function SelectGroup(id, parent, element) 
			{ 
				var elmt = $(element);

				var suppliers = $$("div.selectedTreeElement"); 
				for(i = 0; i < suppliers.length; i++) { suppliers[i].className="treeElement"; }; 
				elmt.className="selectedTreeElement"; 

				$("'.$selectedId.'").value = id;
			} ';

		return $result;
	}
	
	public function GetMaterialsGroupsTree($selectedId)
	{
		$materialsGroups = new Materials();
		$result = $materialsGroups->GetMaterialsGroupsList();
		
		$render .= "<div id='treeElements' style='width:".$this->width."; display:".($this->display)."'>";
		while($row = mysql_fetch_array($result))
		{
			$render .= "<div ";
			if($this->allowSelect)
			{
				$render .= "onClick='SelectGroup(\"".Security::EncodeUrlData($row['MATERIALS_GROUPS_ID'])."\", \"".$row['PARENT_GROUPS_ID']."\", this);'";
			}

			//selectedTreeElement
			if((float)$selectedId == (float)$row['MATERIALS_GROUPS_ID'] || (StaticDatabase::CleanupTheField($selectedId) == $row['UNIQUE_NAME_IDENTIFIER']))
			{
				$render .= " class='selectedTreeElement' ";
			}
			else
			{
				$render .= " class='treeElement' ";
			}
			
			$render .= ">";
			
			if($this->showLinks)
				$render .= "<a href='&groups=".$row['UNIQUE_NAME_IDENTIFIER']."' >";

			$render .= $row['GROUPS_NAME'];
			
			if($this->showLinks)
				$render .= "</a>";
			
			$render .= "</div>";
		}
		$render .= "</div>";
		
		if($this->allowSelect)
		{
			$render .= "<input type='hidden' id='".$this->id."' name='".$this->name."' value='".Security::EncodeUrlData($selectedId)."'>";
		}
		
		return $render;
	}
}

?>