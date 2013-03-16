<?php

  if (@preg_match("/pages/i", $_SERVER['PHP_SELF'])) 
    die ("You can't access this file directly...");

  include_once("CommonBlock.php");
  
  include_once("includes/common/Tag/Lib.Anchor.php");
  include_once("Block.Calendar_Local.php");

  	/**
	 * class Calendar
	 *
	 * Реалізує основні функції роботи із календарем і датами
	 *
	 * @package Blocks.pkg
	 */

	class BlockCalendar extends CommonBlock
	{	
		public $page = NULL;
		
		private $fieldsArray = array( "search_text" => false );

		public $blocksMarkup = "";
		
		public $inputId = "searchText";
		public $inputText = "searchValue";
		
		public static $today = 0;
		
		private $searchText = "";
		
		private $isValidForm = false;
		
		private $formTag = NULL;
		
		public $BlockMode = RENDER_AJAX_SEARCH_FORM; // RENDER_AJAX_SEARCH_FORM ajax search form
							     // RENDER_MATERIALS_SEARCH_INPUT ajax input
							     // RENDER_COMPANIES_SEARCH_INPUT ajax input

		private $search = NULL;
				
		public function __construct()
		{
			$this->localizator = new BlockCalendar_Local();
			$this->search = new Search();
			
			$this->SCodes = self::GetAllowedSCodes();
			
			if($this->linkForListing == "")
			{
				$this->linkForListing = Request::GetRoot()."/search/";
			}
			
			self::$today = mktime(0, 1, 1, date("d"), date("m"), date("Y"));

			parent::__construct();
		}
		
		public static function GetAllowedSCodes()
		{
			return array (
					'ajax_search' => sha1("mAkeThEpReSeArcHreQuest") 
						); 
		}
		
		public static function Today()
		{
			return mktime(0, 1, 1, date("d"), date("m"), date("Y"));
		}
		
		public function ValidateForm()
		{
			$this->isValidForm = false;
			
			if(!in_array(Request::GetSCode(), $this->SCodes))
			{
				$this->isValidForm = false;
				return $this->isValidForm;
			}
			
			$searchText = StaticDatabase::CleanupTheField($_POST[$this->inputId]); //;
			
			return $this->isValidForm;
		}
		
		public static function GetNewsDateFormat($date = '', $url = '')
		{
			$result = '<span class="clbl">';
			if(!is_numeric($date))
				$cdate = strtotime($date);
			else
				$cdate = $date;
				
			$syear = strtotime("01 jan ".date("Y"));
			$needTime = true;
			
			$localizator = new BlockCalendar_Local();

			if($cdate - $syear < 0)
			{
				$result = date("d/M/Y ", $cdate);
				$needTime = false;
			}
			elseif($cdate - self::Today() >= 0)
			{
				$result = $localizator->GetLocalValue('Calendar_Today');
			}
			elseif($cdate - strtotime("-1 day") >= 1)
			{
				$result = $localizator->GetLocalValue('Calendar_Yesterday');
			}
			else
			{
				$result = date("d M ", $cdate);
			}

			if($needTime)
			{	
				$result .= date(" H:m", $cdate);
			}

			return $result;
		}
		
		private function SwitchMode()
		{
			switch($this->BlockMode)
			{

			}
		}

		public function BlockInit()
		{
			parent::BlockInit();

			$this->formTag = new Form();
			$this->name = "search_form";

			$this->SwitchMode();
		}
	}
?>