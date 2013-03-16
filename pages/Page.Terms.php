<?

  if (@preg_match("/pages/i", $_SERVER['PHP_SELF'])) 
    die ("You can't access this file directly...");

	include_once("CommonPage.php");

	include_once("Page.Terms_Local.php");

	class Terms extends CommonPage
	{
		public $pagesItems = array ("Title", "Login", "Password", 
							"Confirm_password", "Email", "First_Name", "Second_Name", 
							"Third_Name", "Company", "Public_Email", "Submit_Text");
		public $pagesMarkup = '';
		
		public $blocks = NULL;
	
		private $isValidForm = false;
		
		private $valuesArray = array();
		
		private $usr;
		
		function __construct() 
		{
			$this->localizator = new Terms_local();

			parent::__construct();
		}
		
		public function PageInit()
		{
			parent::PageInit();
			
			$this->setTitle("<:=Terms_Title=:>");
			
			$this->pagesMarkup .= '
				<div id="termsBlock">
				<:=Terms_Top=:><br>
				<:=Terms_1_Relations=:><br>
				<:=Terms_2_Relations=:><br>
				<:=Terms_3_Language=:><br>
				<:=Terms_4_UseOfService=:><br>
				<:=Terms_5_ServiceUpdate=:><br>
				<:=Terms_6_Confidentiality=:><br>
				<:=Terms_7_ContentOfService=:><br>
				<:=Terms_8_Passwords=:><br>
				<:=Terms_9_Licenses=:><br>
				<:=Terms_10_StopOfConditions=:><br>
				<:=Terms_11_Guaranties=:><br>
				<:=Terms_12_Responsibility=:><br>
				<:=Terms_13_Copyright=:><br>
				<:=Terms_14_OtherContent=:><br>
				<:=Terms_15_UserRights=:><br>
				<:=Terms_16_CommonJurTerms=:>
				</div>';
			
			if($this->isValidPost && Request::GetSCode() == sha1("DoNatEbuTtOnCliCk"))
			{
				$xml= '';

				$xml_encoded=base64_encode($xml); 

				$this->commonScripts = "</script><script>alert('123'); </script>";
			}
			elseif($subpage == "Thanks")
			{
				$this->pagesAjaxMarkup .= "Thank you!";
			}

		}
	}
?>