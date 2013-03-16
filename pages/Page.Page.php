<?

  if (@preg_match("/pages/i", $_SERVER['PHP_SELF'])) 
    die ("You can't access this file directly...");

  include_once("CommonPage.php");
  include_once("Page.Terms_Local.php");
  
  include_once("includes/common/Lib.php");
  include_once("includes/common/Lib.Mailer.php");
  include_once("pages/Block.Login.php");
  
  include_once("includes/DatabaseClasses/Parts.Users.php");

/**
 * class Page
 *
 * Цей класс для загальних сторінок, що оброблюються із бази даних.
 *
 * @package Pages.pkg
 */

class Page extends CommonPage
{
	public $pagesItems = array ("Title", "Login", "Password", 
						"Confirm_password", "Email", "First_Name", "Second_Name", 
						"Third_Name", "Company", "Public_Email", "Submit_Text");
	public $pagesMarkup = '<body>fsdfsdf</body>';
	
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
		$this->localizator = new Terms_local();

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
		elseif($this->usr->LoadUserByLogin($this->valuesArray['login_field']) != NULL)
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
	
	
	/**
	 * Init pages
	 */
	public function PageInit() 
	{
		parent::PageInit();

		$this->pagesMarkup .= "<:=Terms_Of_Use_Title=:>";
		
		$this->pagesMarkup .= "<:=Terms_Preambula=:>";
		
		$this->pagesMarkup .= "<:=Terms_Body=:>";
				
	}
	
}

?>
