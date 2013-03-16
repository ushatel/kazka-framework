<?

  if (@preg_match("/pages/i", $_SERVER['PHP_SELF'])) 
    die ("You can't access this file directly...");

  include_once("CommonPage.php");
  include_once("Page.Registration_Local.php");
  
  include_once("includes/common/Lib.php");
  include_once("includes/common/Lib.Mailer.php");
  //include_once("pages/Block.Login.php");
  
  include_once("includes/DatabaseClasses/Parts.Users.php");

/**
 * class Registration
 *
 * Цей класс сторінки регистрації нового користувача.
 *
 * @package Pages.pkg
 */

class Registration extends CommonPage
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
		$this->localizator = new Registration_local();
		$this->usr = new Users();

		parent::__construct();
	}
	
	/** 
	 * Валідація форми після відправки на сервер
	 *
	 */
	private function ValidateForm() 
	{
		$this->valuesArray["login_field"] = StaticDatabase::CleanupTheField($_POST['login_field']);
		
		if((strlen($this->valuesArray['login_field']) < 3)) 
		{
			$this->fields_array["login"] = "<:=Validation_Login_IsTooShort_Error=:>";
			$this->isValidForm = false;
		}
		elseif(preg_match("/Lost/i",$this->valuesArray["login_field"]) || preg_match("/Login/i", $this->valuesArray["login_field"]) || 
				$this->usr->LoadUserByLogin($this->valuesArray['login_field']) != NULL)
		{
			$this->fields_array["login"] = "<:=Validation_Login_IsExists_Error=:>";
			$this->isValidForm = false;
		}
		else 
		{
			// First main validation
			$this->isValidForm = true;
		}
		
		$this->valuesArray["password_field"]  = StaticDatabase::CleanupTheField($_POST['password_field']);
		$this->valuesArray["password_confirm"] = StaticDatabase::CleanupTheField($_POST['password_confirm']);
		
		if($this->valuesArray["password_field"] != $this->valuesArray["password_confirm"]) 
		{
		    /// !!!! Валідація паролю має бути на стороні клієнта !!!
			if(strlen($this->valuesArray["password_confirm"]) >= 7) 
			{
				$this->fields_array["password_field"] = "<:=Validation_Password_Error=:>";
			}
			else 
			{
				$this->fields_array["password_field"] = "<:=Validation_Password_Length_Error=:>";
			}

			$this->isValidForm = false;
		}
		else 
		{
			$this->isValidForm = true & $this->isValidForm;
		}
		$this->valuesArray["email"] = StaticDatabase::CleanupTheField($_POST['email']);
				
		$emailParse = '/([a-zA-Z0-9_\-\.]+)@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.)|(([a-zA-Z0-9\-]+\.)+))([a-zA-Z]{2,4}|[0-9]{1,3})/i';
		
		if((strlen($this->valuesArray['email']) == 0) || !preg_match($emailParse, $this->valuesArray["email"])) 
		{
			$this->fields_array["email"] = "<:=Validation_Email_Error=:>";
			$this->isValidForm = false;
		}
		else 
		{
			$this->isValidForm = true & $this->isValidForm;
		}
		
		$this->valuesArray["first_name"]   = StaticDatabase::CleanupTheField($_POST['first_name']);

		if((strlen($this->valuesArray['first_name']) < 3)) 
		{ 
			$this->fields_array["first_name"] = "<:=Validation_FirstName_IsTooShort_Error=:>";
			$this->isValidForm = false;
		}
		else 
		{
			$this->isValidForm = true & $this->isValidForm;
		}
		
		$this->valuesArray["second_name"]  = StaticDatabase::CleanupTheField($_POST['second_name']);
		
		if((strlen($this->valuesArray['second_name']) < 3)) 
		{
			$this->fields_array["second_name"] = "<:=Validation_SecondName_IsTooShort_Error=:>";
			$this->isValidForm = false;
		}
		else 
		{
			$this->isValidForm = true & $this->isValidForm;
		}
		
		$this->valuesArray["accept_terms"] = substr(StaticDatabase::CleanupTheField($_POST['accept_terms']), 0, 2);
		if($this->valuesArray["accept_terms"] != "on")
		{
			$this->fields_array["accept_terms"] = "<:=Validation_Terms_Error=:>";
			$this->isValidForm = false;
		}
		else
		{
			$this->isValidForm = true & $this->isValidForm;
		}
		
		$this->valuesArray["third_name"]   = StaticDatabase::CleanupTheField($_POST['third_name']);

		// Це поле заповнюється із довідника компаній
		$this->valuesArray["company_name"] = StaticDatabase::CleanupTheField($_POST['company_name']);
		$this->valuesArray["public_email"] = StaticDatabase::CleanupTheField($_POST['public_email']);
		
		if((strlen($_POST['public_email']) != 0) && !preg_match($emailParse, $this->valuesArray["public_email"])) 
		{
			$this->fields_array["public_email"] = "<:=Validation_Email_Error=:>";
			$this->isValidForm = false;
		}
		else 
		{
			$this->isValidForm = true & $this->isValidForm;
		}
		
		return $this->isValidForm;
	} 
	
	public function ValidateConfirmationForm()
	{
		$this->valuesArray["login_field"] = StaticDatabase::CleanupTheField($_POST['login_field']);
		
		if($this->usr->LoadUserByLogin($this->valuesArray['login_field']) == NULL)
		{
			$this->fields_array["login"] = "<:=Validation_Confirmation_Login_IsNotExists_Error=:>";
			$this->isValidForm = false;
		}
		else 
		{
			$this->isValidForm = true;
		}
		
		return $this->isValidForm;
	}
	
	public function RenderAsterisks($alertText, $isVisible = false) 
	{
		return ( ( ((strlen($alertText) > 0) | $isVisible) && !$this->isValidForm) ? "<font style='color:#FF0000; cursor:default;' title='".$alertText."'><b>*</b></font>" : "");
	}
	
	/**
	 * Init pages
	 */
	public function PageInit() 
	{
		parent::PageInit();

		if(Request::GetSCode() != sha1("ProCeSsCoNfIrMatIonReGisTraTiOnCoDe") && Request::GetSCode() != sha1("ProCeSsPaSsWordReSet") && (!$this->isValidPost || !$this->ValidateForm()) ) {
			// Форму ще не було відправленно на сервер, або вона не пройшла валідацію

			//$this->pagesMarkup = "<:=LOGIN_BLOCK=:>";			
			
			$formTag = new Form();
			
			$this->pagesMarkup .= $formTag->RenderTop();
			
			$hdn = new Hidden();
			$hdn->SetName("__SCode");
			$hdn->SetValue(sha1("pRocEssTheLeGalReGistRation"));

			$this->pagesMarkup .= $hdn->OpenTag();

			$this->pagesMarkup .= "<table style='margin-top:10px; margin-left: 10px;'>";
			
			$this->pagesMarkup .= "<tr><td><:=Login=:>:</td><td style='width:250px'><nobr><input type='text' name='login_field' title='<:=Login=:>' ".
																																		"style='width:96%' value='".$this->valuesArray["login_field"]."' />&nbsp;".
																									$this->RenderAsterisks($this->fields_array["login"])."</nobr></td></tr>";
			$this->pagesMarkup .= "<tr><td><:=Password=:>:</td><td><nobr><input type='password' name='password_field' title='<:=Password=:>' 	 style='width:96%' value='' />&nbsp;".
																									$this->RenderAsterisks($this->fields_array["password_field"])."</nobr></td></tr>";
			$this->pagesMarkup .= "<tr><td><:=Confirm_password=:>:</td><td><nobr><input type='password' name='password_confirm' title='<:=Confirm_password=:>' ". 
																																		"style='width:96%' value='' /></td></tr>";
			$this->pagesMarkup .= "<tr><td><:=Email=:>:</td><td><nobr><input type='text' name='email' title='<:=Email=:>' 				 style='width:96%' value='".$this->valuesArray["email"]."' />&nbsp;".
																									$this->RenderAsterisks($this->fields_array["email"])."</nobr></td></tr>";
			$this->pagesMarkup .= "<tr><td><:=First_Name=:>:</td><td><nobr><input type='text' name='first_name' title='<:=First_Name=:>' 		 style='width:96%' value='".$this->valuesArray["first_name"]."' />&nbsp;".
																									$this->RenderAsterisks($this->fields_array["first_name"])."</nobr></td></tr>";
			$this->pagesMarkup .= "<tr><td><:=Second_Name=:>:</td><td><nobr><input type='text' name='second_name' title='<:=Second_Name=:>'  	 style='width:96%' value='".$this->valuesArray["second_name"]."' />&nbsp;".
																									$this->RenderAsterisks($this->fields_array["second_name"])."</nobr></td></tr>";
			$this->pagesMarkup .= "<tr><td><:=Third_Name=:>:</td><td><input type='text' name='third_name' title='<:=Third_Name=:>' 		 style='width:96%' value='".$this->valuesArray["third_name"]."' /></td></tr>";
			$this->pagesMarkup .= "<tr><td><:=Company=:>:</td><td><input type='text' name='company_name' title='<:=Company=:>'     		 style='width:96%' value='".$this->valuesArray["company"]."' /></td></tr>";
			$this->pagesMarkup .= "<tr><td><:=Public_Email=:>:</td><td><nobr><input type='text' name='public_email' title='<:=Public_Email=:>'   style='width:96%' value='".$this->valuesArray["public_email"]."' />&nbsp;".
																									$this->RenderAsterisks($this->fields_array["public_email"])."</nobr></td></tr>";
			$this->pagesMarkup .= "<tr><td colspan='2'><input type='checkbox' name='accept_terms' id='accept_terms' style='margin:5px; margin-top:10px;' ".($this->valuesArray["accept_terms"] == 'on' ? 'checked="checked"' : '')."><:=Terms_Of_Use_Checkbox=:>".$this->RenderAsterisks($this->fields_array['accept_terms'])."<td></tr>";
			$this->pagesMarkup .= "<tr><td colspan='2' style='padding-top:5px;'>".$formTag->RenderSubmitButton("<:=Submit_Text=:>")."</td></tr>";
			 
			$this->pagesMarkup .= "</table>";
			
			$this->pagesMarkup .= $formTag->RenderBottom();
		}
		elseif(	Request::GetSCode() == sha1("pRocEssTheLeGalReGistRation") ) {
			
			//Форму реєстрації нового користувача вже було відправлено до серверу та вона вже пройшла базову валідацію

			$this->usr->login = $this->valuesArray["login_field"];
			$this->usr->password = $this->valuesArray["password_field"];
			$this->usr->email = $this->valuesArray["email"];
			$this->usr->firstName = $this->valuesArray["first_name"];
			$this->usr->secondName = $this->valuesArray["second_name"];
			$this->usr->thirdName = $this->valuesArray["third_name"];
			$this->usr->companyName = $this->valuesArray["companyName"];
			$this->usr->email2 = $this->valuesArray["email2"];
			
			$this->usr->ip_last = Request::$ip;
			$this->usr->ip_current = Request::$ip;
			
			$this->pagesMarkup .= "<div style='padding: 30px; text-align: center; width: 700px; '>";
			if($this->usr->CreateUser() > 0)
			{
				$bl = new BlockLogin();

				$bl->RegistrationLetter('', $this->usr);

				$this->pagesMarkup = "<:=Thank_You_Registering=:>";
			}
			else
			{
				$this->pagesMarkup = "<:=Registration_Error=:>";
			}

			$this->pagesMarkup .= "</div>";
			
		}
		elseif( Request::GetSCode() == sha1("ProCeSsCoNfIrMatIonReGisTraTiOnCoDe") || Request::GetSCode() == sha1("ProCeSsPaSsWordReSet")) 
		{ 
		  // Підтвердження реєстрації
		  	$SCValue = StaticDatabase::CleanupTheField($_POST['__SCValue']);

			$this->usr->login = $this->valuesArray["login_field"];
			if(!$this->isValidPost || (float)$_POST['sent'] < 1 && !$this->ValidateConfirmationForm())
			{
				// Have got the user activation code. Need the login name
				$formTag = new Form();

				Session::IncErrorCount();

				$this->pagesMarkup = $formTag->RenderTop();

				$hdn = new Hidden();
				$hdn->SetName("__SCode");
				$hdn->SetValue(Request::GetSCode());

				$this->pagesMarkup .= $hdn->OpenTag();

				$hdn = new Hidden();
				$hdn->SetName("__SCValue");
				$hdn->SetValue($_GET['__SCValue']);

				$this->pagesMarkup .= $hdn->OpenTag();
				$this->pagesMarkup .= "<table style='padding: 10px;'>";

				$this->pagesMarkup .= "<tr><td colspan='2' style='text-align:center'><:=Confirmation_Submit_Code_Validation=:></td></tr>";

				$this->pagesMarkup .= "<tr><td><:=Login=:>:</td><td style='width:250px'><nobr><input type='text' name='login_field' title='<:=Login=:>' ".
												"style='width:96%' value='' />&nbsp;".$this->RenderAsterisks($this->fields_array["login"])."</nobr></td></tr>";

				$this->pagesMarkup .= "<tr><td colspan='2' style='text-align:right'>".$formTag->RenderSubmitButton("<:=Submit_Text=:>")."</td></tr>";

				$this->pagesMarkup .= "</table>";

				$this->pagesMarkup .= $formTag->RenderBottom();
			}
			elseif(Request::GetSCode() == sha1("ProCeSsPaSsWordReSet") )
			{
				$formTag = new Form();

				Session::IncErrorCount();

				$hdn = new Hidden();
				$hdn->SetName("__SCode");
				$hdn->SetValue(sha1("ProCeSsPaSsWordReSet"));

				$changePassword = new Login();
				$changePassword->page = $this;
				$changePassword->login = $this->usr->login;
				$changePassword->BlockMode = CHANGE_PASSWORD;
				$changePassword->blockName = "Change the password";

				$this->blocks['CHANGE_PASSWORD'] = $changePassword;

				$changePassword->BlockInit();

				$tag = "<:=CHANGE_PASSWORD=:>";
				if(!$this->isAjaxRequest)
					$this->pagesMarkup = $tag;
				else
					$this->pagesAjaxMarkup = $tag;
					
				//echo $SCValue." ".$this->usr->CheckConfirmationVariable($SCValue);

			}
			else
			{
				Session::ClearErrorCount();

				if($this->usr->CheckConfirmationVariable($SCValue) && $this->usr->ActivateUser())
				{
					$this->pagesMarkup = "<:=Confirmation_Submit_Code_Validation_OK=:>";
				}
				else 
				{
					$this->pagesMarkup = "<:=Confirmation_Submit_Code_Validation_Error=:>";
				}
			}
		}
		
	}
	
}

?>
