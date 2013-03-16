<?php

  if (@preg_match("/pages/i", $_SERVER['PHP_SELF'])) 
    die ("You can't access this file directly...");

	include_once("CommonPage.php");

	include_once("Page.Profile_Local.php");

	include_once("Block.Login.php");	
    include_once("Block.Projects.php");
    include_once("Block.Materials.php");
    include_once("Block.News.php");
    include_once("Block.Friends.php");

	class Profile extends CommonPage
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
			$this->localizator = new Profile_local();

			if(!Request::$IsAJAX)
			{
				$lBlock = new Login();
				$lBlock->blockName = "Login Block";
				$this->blocks["LOGIN_BLOCK"] = $lBlock;// new Login();
			}
			elseif($this->isValidPost)
			{
			}

			parent::__construct();
		}
		
		private function SwitchBlock()
		{
			$mainBlock = NULL;
			
			switch(Request::GetSCode())
			{
				
				case sha1("sAveFilEtoDatAbaSe"):
				
					$mainBlock = new BlockAlbum();
					$mainBlock->page = $this;
				
				break;

				case sha1("UsErFriEndS"):
					
					$mainBlock = new BlockFriends();
					$mainBlock->page = $this;
					$mainBlock->BlockMode = RENDER_FRIENDS_GRID;
					$mainBlock->blockName = "Render users friends";
					$mainBlock->SetUserId(Session::GetUserId());

				break;
				
				case sha1("ChaNgEpaSswoRdCliCk"):
					
					$mainBlock = new Login();
					$mainBlock->page = $this;
					$mainBlock->BlockMode = CHANGE_PASSWORD;
					$mainBlock->blockName = "Change the password";

				break;

				case sha1("mAkeThEpReSeArcHreQuest"):
	 				
					$mainBlock = new Login();
					$mainBlock->page = $this;
					$mainBlock->BlockMode = PROFILE_FIELDS;
					$mainBlock->blockName = "Render the profile fields";

				break;
				
				case sha1("SavEpRofIleFilE"):

					$mainBlock = new Login();
					$mainBlock->page = $this;
					$mainBlock->BlockMode = PROFILE_FIELDS;
					$mainBlock->blockName = "Save Profile";
					
				break;

				case sha1("SelEcTComPanY"):
				
					$mainBlock = new BlockSearch ();
					$mainBlock->page = $this->page;
					$mainBlock->BlockMode = RENDER_COMPANIES_SEARCH_INPUT;
					$mainBlock->inputId = "companiesInput";

				break;

			}
			
			$mainBlock->BlockInit();

			$this->blocks['MAIN_BLOCK'] = $mainBlock;
			
			if($this->blocks)
			{
				$this->pagesAjaxMarkup .= "<:=MAIN_BLOCK=:>";
			}
			
		}


		public function PageInit()
		{
			parent::PageInit();

			if(Session::GetUserId() < 1 && Request::$identifier == 'Lost')
			{	// Resend activation letter
				$lostBlock = new Login();
				$lostBlock->page = $this;
				$lostBlock->BlockMode = LOGIN_LOST;
				$lostBlock->blockName = "Render the lost fields";
				
				$lostBlock->BlockInit();
				
				$this->blocks['LOST_BLOCK'] = $lostBlock;
				
				$text = "<:=LOST_BLOCK=:>";
				
				if(!$this->isAjaxRequest)
				{
					$this->pagesMarkup = $text;
				}
				else
				{
					$this->pagesAjaxMarkup = $text;
				}
			}
			elseif(Session::GetUserId() < 1)
			{
				$profileBlock = new Login();
				$profileBlock->page = $this;
				$profileBlock->BlockMode = LOGIN_FORM;
				$profileBlock->blockName = "Render the login fields";

				$profileBlock->BlockInit();
				
				$this->blocks['PROFILE_BLOCK'] = $profileBlock;

				if(!$this->isAjaxRequest)
				{
					$this->pagesMarkup .= "<:=PROFILE_BLOCK=:>";
				}
				else
				{
					$this->pagesAjaxMarkup .= "<:=PROFILE_BLOCK=:>";
				}
				
				if(preg_match("/Login/i", Request::$identifier))
				{
					$this->SetTitle($this->GetLocalValue('Login_Title'));
				}
			}
			elseif(!Request::$IsAJAX)
			{
				$profileBlock = new Login();
				$profileBlock->page = $this;
				$profileBlock->BlockMode = PROFILE_FIELDS;
				$profileBlock->blockName = "Render the profile fields";

				$profileBlock->BlockInit();

				$this->blocks['PROFILE_BLOCK'] = $profileBlock;

				$this->pagesMarkup .= "<:=PROFILE_BLOCK=:>";
			}
			else
			{	//SavEpRofIleFilE
				if($this->isValidPost)
				{
					$this->SwitchBlock();
				}
				else
				{
					header('HTTP/1.1 500 Internal Server Error');					
				}

				//$this->page->pagesAjaxMarkup .= " <script>alert('Hello world!');</script>Have sent from server!";
			}

		}
	}
?>