<?php

  if (@preg_match("/pages/i", $_SERVER['PHP_SELF'])) 
    die ("You can't access this file directly...");

	include_once("CommonPage.php");

	include_once("Page.About_Local.php");

	class About extends CommonPage
	{
		public $pagesItems = array ("Title", "Login", "Password", 
							"Confirm_password", "Email", "First_Name", "Second_Name", 
							"Third_Name", "Company", "Public_Email", "Submit_Text");
		public $pagesMarkup = '';
		
		public $blocks = NULL;
		
		private $fields_array = array("login" => false, 
				"email" => false, "password_field" => false, 
				"first_name" => false, "second_name" => false, 
				"third_name" => false, "company_name" => false,
				"public_email" => false);
	
		private $isValidForm = false;
		
		private $valuesArray = array();
		
		private $usr;
		
		function __construct() 
		{
			$this->localizator = new About_local();

			if(!Request::$IsAJAX)
			{
			}
			elseif($this->isValidPost)
			{
			}

			parent::__construct();
		}
		
		public function PageInit()
		{
			parent::PageInit();
			
			$this->pagesMarkup = '<:=About_Title=:><br><br><:=About_Text=:>';

		}
	}
?>