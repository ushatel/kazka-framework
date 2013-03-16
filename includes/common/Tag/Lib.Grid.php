<?php

	if(@preg_match("includes/i", $_SERVER['PHP_SELF']))
		die("You can't access this file directly...");
		
	include_once("Lib.Tag.php");

	/**
	 *  Library is the package with the common library functionality
	 *
	 *  @package Library.pkg
	 */
	 
	/**
	 *  Class Field
	 *
	 *  Цей клас реалізує колонки для гріда
	 *  
	 *  @package Library.pkg
	 */
	 
	class Field extends Tag
	{
		public $tagName = "td"; // для табличного типу
	
		public $id = "";
		public $name = "";
		public $value = "";
		public $type = "text"; // text, object (checkbox, div, a, select, radio)
		public $hidden = false;
		public $width = "";
		
		public $cssField = "";
				
		public function __construct()
		{
			$this->id = "fid_".rand(10001, 99999);
		}
	}
	
	/**
	 * Цей клас зберігає поля для налаштування строк 
	 */
	class RowProperties
	{
		public $id = 0;
		public $type = "property";
		public $hidden = false; // not render
		
		public $clientId = "";
		
		public $isSelected = false;
		
		public $style = "";

		public $cssSelectedRow = "";
		public $cssHoverRow = "";
		public $cssRow = "";
		
		public $width = "";
		
		function __construct()
		{
			if(strlen($this->clientId) < 1) 
				$this->clientId = "row_".rand(10101, 98989);
				
		}
	}
	 
	/**
	 *  Class Grid
	 *
	 *  Цей клас відрисовує таблицю із даними 
	 *
	 *  @package Library.pkg
	 */

	class Grid extends Tag
	{
		public $tagName = "table"; // для табличного гріда
		
		public $isBlockType = false;
		
		public $tagAttributes = NULL;
		
		public $name = "common_grid";
		
		public $fieldsArray = array (); // Зберігає колонки для гріда
		public $rowsArray = array();    // Зберігає строки
		
		private $formTag = NULL;
		
		public $needDrawForm = false;
		public $needDrawHeaders = false;
		
		public $cssColumn = "grid_column";
		
		public $cssHeaderRow = "grid_row_header";
		public $cssSelectedRow = "grid_row_selected";
		public $cssHoverRow = "grid_row_onhover";
		public $cssRow = "grid_row";
		
		public $cssGrid = "grid";
		
		public $width = "";
		
		public function __construct()
		{
			
		}
		
		public function RenderTop()
		{
			
			if($this->needDrawForm)	
			{

				$this->formTag = new Form();
				$this->formTag->name = $this->name."_".rand(10101, 99999);
			
				$result = $this->formTag->RenderTop();
			}
			
			$result .= "<div class='".$this->cssGrid."' ".$this->SetStyleAttribute("width", $field->width, 1).">";
			
			// Grids header
			if($this->needDrawHeaders)
			{
				$result .= $this->RenderHeader();
			}
			
			return $result;
		}
		
		private function SetFieldsCss($field, $field_key)
		{
			$result = "";
			
			if(strlen($field->cssField) > 0)
			{
				$result = " class='".$field->cssField."' ";
			}
			elseif(strlen($this->fieldsArray[$field_key]->cssField) > 0) 
			{
				$result = " class='".$this->fieldsArray[$field_key]->cssField."' ";
			}
			elseif(strlen($this->cssColumn) > 0)
			{
				$result = " class='".$this->cssColumn."' ";
			}
			
			return $result;
		}
		
		private function SetFieldsWidth($width, $key, $drawStyle = false)
		{
			$result = "";
			if(strlen($width) > 0)
			{
				$result = "width:".$width.";";
			}
			elseif(strlen($this->fieldsArray[$key]->width) > 0) // Перевіряємо чи було вказано ширину у заголовці
			{
				$result = "width:".$this->fieldsArray[$key]->width.";";
			}
						
			if($drawStyle)
			{
				$result = " style='".$result."' ";
			}
			
			return $result;
		}
		
		private function RenderRowProps($props, $isHeader = false)
		{
			if($props != NULL && get_class($props) == 'RowProperties')
			{
				$result = "<div";
				
				if(!$props->isSelected)
				{
					if(strlen($props->cssClass) > 0)
					{
						$result .= ' class="'.$props->cssClass.'"';
					}
					elseif(strlen($this->cssRow) > 0)
					{
						$result .= ' class="'.$this->cssRow.'"';
					}
				}
				elseif($props->isSelected)
				{
					if(strlen($props->cssSelectedRow) > 0)
					{
						$result .= " class='".$this->cssSelectedRow."'";
					}
					elseif(strlen($this->cssSelectedRow) > 0)
					{
						$result .= " class='".$this->cssSelectedRow."'";
					}
					elseif(strlen($props->cssClass) > 0)
					{
						$result .= ' class="'.$props->cssClass.'"';
					}
					elseif(strlen($this->cssRow) > 0)
					{
						$result .= ' class="'.$this->cssRow.'"';
					}
				}
				
				if($isHeader == 1)
				{ 
					$result .= ' class="'.$this->cssHeaderRow.'"';
				}
				
				if(strlen($props->style) > 0)
				{
					$result .= ' style="'.$props->style.'"';
				}

				if(strlen($props->clientId) > 0)
				{
					$result .= ' id="'.$props->clientId.'"';
				}

				$result .= ">";
			}
			else
			{
				$css = "";
				if($isHeader == 1)
				{ 
					$css = $this->cssHeaderRow;
				}

				$result = "<div id='".$rowId."' class='".$css."'>";
			}
			
			return $result;
		}
		
		public function RenderHeader()
		{
			$result = $this->RenderRowProps($row['ROW_PROPERTIES'], true);

			foreach($this->fieldsArray as $fkey => $fvalue)
			{
				$result .= $this->RenderField($fkey, $fvalue);
			}

			$result .= "</div>";

			return $result;
		}
		
		public function RenderRow($row)
		{
			$result = $this->RenderRowProps($row['ROW_PROPERTIES']);

			foreach($row as $fkey => $fvalue)
			{
				$result .= $this->RenderField($fkey, $fvalue);
			}
			
			$result .= "</div>";
		
			return $result;
		}
		
		public function RenderField($fkey, $fvalue)
		{
			//$field = $row[$fkey]; // my field value
			$field = $this->fieldsArray[$fkey];

			if($fkey != 'ROW_PROPERTIES' || get_class($field) == 'Field')
			{
				if(!$field->hidden && !$fvalue->hidden /*Column*/ )
				{
					switch($fvalue->type)
					{
						case 'text':

							$result .= "<div ".$this->SetFieldsCss($fvalue, $fkey)." style='".$this->SetFieldsWidth($fvalue->width, $fkey, false)."'>".$fvalue->value."</div>"; // $this->SetStyleAttribute("display", "inline", 2)
							
						break;

						case 'object':

							$obj = $fvalue->value;
							$result .= "<div ".$this->SetFieldsCss($fvalue, $fkey)." style='".$this->SetFieldsWidth($fvalue->width, $fkey, false)."'>".$obj->OpenTag().$obj->CloseTag()."</div>"; //$this->SetStyleAttribute("display", "inline", 2)

						break;
					}
				}
			}
			
			return $result;
		}
		
		public function RenderBody()
		{
			$result = "";
			
			foreach($this->rowsArray as $row)
			{
				$result .= $this->RenderRow($row);
			}
			
			return $result;
		}
		
		public function RenderBottom()
		{
			$result = "";
		
			$result .= "</div>";
			
			if($this->needDrawForm)
			{
				$result .= $this->formTag->RenderBottom();
			}
			
			return $result;
		}
	}	 
?>