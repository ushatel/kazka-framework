<?php

  if (@preg_match("/pages/i", $_SERVER['PHP_SELF'])) 
    die ("You can't access this file directly...");


  include_once("CommonBlock.php");
  
  include_once("Block.Projects_Local.php");
  include_once("Block.Calendar.php");
  include_once("Block.Album.php");
  include_once("Block.Companies_Local.php");
  include_once("includes/common/Lib.php");
  include_once("includes/common/Tag/Lib.Grid.php");
  
  include_once("includes/DatabaseClasses/Parts.Companies.php");


   /** 
	* class BlockCompanies
	*
	* Служит для работы с компаниями
	*
	* @package Blocks.pkg
	*/
	
	class BlockCompanies extends CommonBlock
	{
		public $page = NULL;
		
		private $fieldsArray = array 
			(
			"company_name_field" => false,
			"company_edrpou_field" => false,
			"company_address_field" => false,
			"company_office_address_field" => false,
			"unique_name_field" => false
			);
			
		public $isReadOnly = false;

		public $BlockMode = COMPANIES_LIST;   // EDIT_COMPANY 	- renders the form of the new company
											  // COMPANIES_LIST - renders the companies for the user
											  // COMPANIES_PROFILE - renders the companies info for the company
											  
		public $companiesWindow = 15;

		public function __construct()
		{
			$this->localizator = new BlockCompanies_Local();
			$this->companies = new Companies();
			
			$this->SCodes = self::GetAllowedSCodes();

			parent::__construct();
		}
		
		public static function GetAllowedSCodes()
		{
			return array (sha1("AdDinGtHeneWcoMpaNy"), 'ajax_profile' => sha1("ComPanIesProFile"), 'ajax_editcompany' => sha1("AjAxComPanYeDit") ,
				'ajax_search' => sha1("mAkeThEpReSeArcHreQuest"), 'ajax_savecompany' => sha1("AjAxComPanYsAve"));
		}
		
		private function ValidateForm()
		{
			$this->isValidForm = false;

			if(!in_array(Request::GetSCode(), $this->SCodes))
			{
				return $this->isValidForm;
			}

			$this->companies->name = StaticDatabase::CleanupTheField($_POST['company_name_field']);
			
			if(strlen($this->companies->name) < 3)
			{
				$this->fieldsArray["company_name_field"] = '<:=Validation_Company_name_IsTooShort_Error=:>';
				$this->isValidForm = false;
			}
			else 
			{
				//First form validation
				$this->isValidForm = true;
			}
			
			$new_unique_name_identifier = StaticDatabase::CleanupTheField($_POST['unique_name_field']);
			$error = $this->companies->unique_name_identifier." _ ".$new_unique_name_identifier;
			$compNew = new Companies();
			
			$postBack = StaticDatabase::CleanupTheField($_POST['company_saved'] == sha1("coMpaNySaVed"));
			
			if($postBack && strlen($new_unique_name_identifier) < 3 || 
				($this->companies->unique_name_identifier != $new_unique_name_identifier) && $compNew->ValidateUniqueIdentifier($new_unique_name_identifier))
			{

				///echo $this->companies->unique_name_identfier;
				if(strlen($this->companies->unique_name_identifier) < 3)
				{
					$this->companies->unique_name_identifier = Operations::Translator($this->companies->name);
					$this->fieldsArray["unique_name_field"] = '<:=Validation_Check_Company_Name=:>'.$error;	
				}
				else
				{
					$this->fieldsArray["unique_name_field"] = '<:=Validation_Company_Name_IsNot_Unique=:>';
				}
				$this->isValidForm = false;
			}
			else 
			{
				$this->companies->unique_name_identifier = $new_unique_name_identifier;
				$this->isValidForm = $this->isValidForm & true;
			}
			
			$countryArray = preg_split("/_/i", StaticDatabase::CleanupTheField($_POST['country_name_field']), 2);
			
			if(!is_numeric($countryArray[0]) || strlen($countryArray[1]) != 2)
			{
				$this->fieldsArray["country_name_field"] = "<:=Validation_Company_name_Error=:>";
				$this->isValidForm = false;
			}
			else 
			{
				$this->companies->country_name = $countryArray[1];
				$this->companies->countries_id = (int)$countryArray[0];
			
				$this->isValidForm = $this->isValidForm & true;
			}

			$this->companies->city_name = StaticDatabase::CleanupTheField($_POST['city_name_field']);
			$this->companies->edrpou_taxpnum = StaticDatabase::CleanupTheField($_POST['edrpou_field']);
			$this->companies->mfo = StaticDatabase::CleanupTheField($_POST['mfo_field']);
			$this->companies->swift = StaticDatabase::CleanupTheField($_POST['swift_field']);
			$this->companies->eori_code = StaticDatabase::CleanupTheField($_POST['eori_code']);
			$this->companies->iban = StaticDatabase::CleanupTheField($_POST['iban_field']);
			$this->companies->account = StaticDatabase::CleanupTheField($_POST['account_number_field']);
			$this->companies->isin = StaticDatabase::CleanupTheField($_POST['isin_field']);
			$this->companies->address = StaticDatabase::CleanupTheField($_POST['address_field']);
			$this->companies->physical_address = StaticDatabase::CleanupTheField($_POST['physical_address_field']);
			$this->companies->comment = StaticDatabase::CleanupTheField($_POST['company_comment']);
			$this->companies->zip_code = "";
			$this->companies->vat_code = StaticDatabase::CleanupTheField($_POST['vat_code_field']);
			$this->companies->phone_main = StaticDatabase::CleanupTheField($_POST['phone_main']);
			$this->companies->phone_mob = StaticDatabase::CleanupTheField($_POST['phone_mob']);
			
			$this->companies->common_www = StaticDatabase::CleanupTheField($_POST['common_www']);
			
			if(strlen($this->companies->common_www) > 0 )
			{
				$hdl = NULL;
				try{
					$hdl = @fopen($this->companies->common_www, "r");
					if($hdl != NULL)
					{
						@fclose($hdl);
						$hdl = true;
					}
					else
						$hdl = false;
				}
				catch(Exception $e)
				{
					$hdl = false;
				}
				
				if(!$hdl)	
				{
					$this->fieldsArray['common_www'] = '<:=Validation_WWW_Error=:>';
					$this->isValidForm = false;					
				}
			}
			else
			{
				if($hdl != NULL) fclose($hdl);
				
				$this->isValidForm = $this->isValidForm & true;
			}
			
			$this->companies->common_email = StaticDatabase::CleanupTheField($_POST['common_email']);
							
			$emailParse = '/([a-zA-Z0-9_\-\.]+)@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.)|(([a-zA-Z0-9\-]+\.)+))([a-zA-Z]{2,4}|[0-9]{1,3})/i';
		
			if((strlen($this->companies->common_email) > 0) && !preg_match($emailParse, $this->companies->common_email)) 
			{
				$this->fieldsArray["common_email"] = "<:=Validation_Email_Error=:>";
				$this->isValidForm = false;
			}
			else
			{
				$this->isValidForm = $this->isValidForm & true;
			}

			return $this->isValidForm;
		}
		
		private function RenderAlbum($companiesId = 0, $albumsId = 0)
		{
			$album = new BlockAlbum();
			$album->page = $this->page;
			
			$album->selectedObjectsId = (float)$companiesId;
			$album->selectedEntityId = 6;
			$album->selectedAlbumsId = (float)$albumsId;
			$album->isReadOnly = $this->isReadOnly;
			$album->albumsHeight = 150;
			$album->albumsWidth = 723;
			$album->BlockMode = RENDER_COMMON_ALBUM;
			$album->BlockInit();
			
			if(!$this->isAjaxRequest)
				$this->blocksMarkup .= "<br/><br/><:=BLOCK_ALBUM=:>";
			else
				$this->blocksAjaxMarkup .= "<:=BLOCK_ALBUM=:>";

			$this->blocks['BLOCK_ALBUM'] = $album;
		}
		
		private function CompaniesProfile()
		{
			$company = new Companies();
			$company->unique_name_identifier = Request::$identifier;
			$company->ValidateUniqueIdentifier();
			
			$this->isReadOnly = !($company->updated_login > 0 && Session::GetUserId() == $company->updated_login || Session::IsSuperAdmin());
			$this->CompaniesFields();
			
			//Render Projects
			$projectsList = new BlockProjects();
			$projectsList->page = $this;
			$projectsList->BlockMode = GET_PROJECTS;
			$projectsList->SetCompaniesId($this->companies->companies_id);

			$projectsList->BlockInit();
			
			$this->blocks['BLOCK_LIST_OF_PROJECTS'] = $projectsList;
			
			$this->blocksMarkup .= "<:=BLOCK_LIST_OF_PROJECTS=:>";
						
			$prjBlock = new BlockMaterials();
			$prjBlock->page = $this;
			$prjBlock->BlockMode = RENDER_MATERIALS_GRID;
			$prjBlock->blockName = "LATEST_MATERIALS";
			$prjBlock->SetCompaniesId($company->companies_id); // ідентифікатор компанії
			$prjBlock->isSupplier = true; 
			$prjBlock->BlockInit();

			$this->blocks['LATEST_MATERIALS'] = $prjBlock;
			
			if(!$this->isAjaxRequest)
			{
				$this->page->commonScripts .= ' Objects.Environment["COMPANIES_ID"] = "'.Security::EncodeUrlData($company->companies_id).'"; ';
			}

			if(!$this->isReadOnly)
				$this->blocksMarkup .= "<a onClick='AjaxMaterialClick(); return false;' style='margin-left: 5px;' class='ajaxLink' href='/Materials/'><:=Company_Add_Material=:></a><br>";

			$this->blocksMarkup .= "<:=LATEST_MATERIALS=:>";

			$this->RenderAlbum($this->companies->companies_id, (float)Security::DecodeUrlData($_POST['ALBUMS_ID']));
		} 
		
		private function CompaniesFields()
		{			
			$countryName = $this->companies->LoadCountryName();
			
			$this->page->SetTitle($this->companies->name);
			
			$this->blocksMarkup .= "<div id='companiesFields' >";
			
			$this->blocksMarkup .= "<div class='row'><div class='title'><:=Company_Title=:></div><div style='width: 77.7%;'><span style='float:left;width:557px;display:block;overflow:hidden;'>{$this->companies->name} ({$this->companies->unique_name_identifier})</span></div></div>";
			if(strlen($this->companies->common_www) > 0)
				$this->blocksMarkup .= "<div class='row'><div class='title'><:=Company_WWW=:></div><div style='width: 557px; overflow:hidden;'><a href='{$this->companies->common_www}'>{$this->companies->common_www}</a></div></div>";

			$this->blocksMarkup .= "<div class='row' style='height: 100px;'><div class='title'><:=Company_Comment=:></div><div style='height: 100px; overflow: auto; width: 557px;'>".preg_replace("/[\n]/i", "<br>", $this->companies->comment)."</div></div>"; //.preg_replace("/[\\\]/", "", preg_replace("/[\n]/i", "<br>", $this->companies->comment))
			
			$this->blocksMarkup .= "<span onClick='Slide(310, \"commonFields\", \"<:=Company_Close=:>\", this); return false;' style='display:inline-block; width:100%; text-align:right; cursor: pointer; font-size: 13px;'><:=Company_Open=:></span><div id='commonFields' style='display: none; font-size:15px;' >"; 
			$this->blocksMarkup .= "<div class='row'><div><span id='edrpou'><:=EDRPOU=:></span><span id='reg_number' style='display:none'><:=Registration_Number=:></span></div><div>{$this->companies->edrpou_taxpnum}</div></div>";
			$this->blocksMarkup .= "<div class='row'><div><:=IBAN_CODE=:></div><div>{$this->companies->iban}</div></div>";
			$this->blocksMarkup .= "<div class='row'><div><:=Account_Number=:></div><div>{$this->companies->account}</div></div>";
			$this->blocksMarkup .= "<div class='row'><div><:=VAT_CODE=:></div><div>{$this->companies->vat_code}</div></div>";
			$this->blocksMarkup .= "<div class='row'><div><:=ISIN_CODE=:></div><div>{$this->companies->isin}</div></div>";
			$this->blocksMarkup .= "<div class='row'><div><:=EORI_CODE=:></div><div>{$this->companies->eori_code}</div></div>";
			$this->blocksMarkup .= "<div class='row'><div><:=Country_Name=:></div><div>{$countryName}</div></div>";
			$this->blocksMarkup .= "<div class='row'><div><:=City_Name=:></div><div>{$this->companies->city_name}</div></div>";
			$this->blocksMarkup .= "<div class='row'><div><:=Physical_ADDRESS=:></div><div>{$this->companies->physical_address}</div></div>";
			$this->blocksMarkup .= "<div class='row'><div><:=Law_ADDRESS=:></div><div>{$this->companies->address}</div></div>";
			$this->blocksMarkup .= "<div class='row'><div><:=Main_Phone=:></div><div>{$this->companies->phone_main}</div></div>";
			$this->blocksMarkup .= "<div class='row'><div><:=Mobile_Phone=:></div><div>{$this->companies->phone_mob}</div></div>";
			$this->blocksMarkup .= "<div class='row'><div><:=Common_WWW=:></div><div><a href='{$this->companies->common_www}' target='_blank'>{$this->companies->common_www}</a></div></div>";
			$this->blocksMarkup .= "</div>";

			if(Session::GetUserId() > 0) 
			{
				$link = new Anchor();
				
				$localValue = $this->GetLocalValue('Company_Edit');

				$link->SCode = $this->SCodes['ajax_savecompany'];
				$link->title = $localValue;
				$link->href = Request::$url;
				$link->hrefAJAX = Request::$url;
				$link->isTraditionalHref = false;
				$link->getParamsValues = true;
				$link->applyScripts = false;
				$link->class = "ajaxLink";
				$link->onClick = "EditCompanyClick('".$localValue."', '".Security::EncodeUrlData($this->companies->companies_id)."'); return false; ";
				
				$this->blocksMarkup .= "<div >".$link->OpenTag().$localValue.$link->CloseTag()."</div>";
			}

			$this->blocksMarkup .= "</div>";
		}
		
		public static function InitScripts()
		{
			$result = ' function EditCompanyClick(title, companyId, del, obj) { 

					var parameters = { 	
						__SVar  	: Objects.Security.secureServerVar, 
		  				__SCode 	: "'.sha1("ComPanIesProFile").'", 
				  		__ClientVar : Objects.Security.createSecureVar(),
				  		IS_AJAX 	: "TRUE"
				  	}
					
					if(companyId == null)
					{
						if(obj != null)
							var companyId = $(obj).parentNode.readAttribute("companyId");
					}
					
					if(companyId != null)
					{
						parameters["COMPANIES_ID"] = companyId;
					}
					
					if(del != null && del > 0)
					{
						parameters["COMPANIES_DELETE"] = 1;
					}

					var rq = new Ajax.Request(document.location.href, {
	
						parameters : parameters,
				  	
			  			onCreate: function ()
			  			{
							Objects.BubbleDiv.title = title;
							Objects.BubbleDiv.height = 500;
							Objects.BubbleDiv.width = 591;
							Objects.BubbleDiv.show();
			  			},

			  			onFailure: function ()
			  			{	
			  			},

			  			onSuccess: function(response) 
			  			{
			  				try
			  				{	 
				  				var rObject = Objects.Security.validateResponse(response.responseJSON);

				  				if(rObject != null && rObject.isSecured)
				  				{
					  				Objects.BubbleDiv.refresh(rObject.text);
							
					  				if(rObject.scripts.length > 0)
						  				eval(rObject.scripts[0]); 

									if(this.successMethod)
										eval(this.successMethod);
										
									if(obj != null && del == 1)
									{
											var row = obj.parentNode.parentNode;

											if(Prototype.Browser.IE)
												row.outerHTML = "";
											else
												row.remove();
									}
								}
								else
								{
									alert(rObject + "_" + response.responseText);
								}
		
							}
							catch(ex)
							{
								FireError(ex);
							}
		    				}
    						});
					 }';
			
			return $result;
		}
		
		private function EditCompanyForm()
		{
			$formTag = new Form();

			if(!$this->isAjaxRequest)
			{
				$this->blocksMarkup = $formTag->RenderTop();

				$hdn = new Hidden();
				$hdn->SetName("__SCode");
				$hdn->SetValue(sha1("AdDinGtHeneWcoMpaNy"));
				
				$this->blocksMarkup .= $hdn->OpenTag();
			}
			
			$postBack = (StaticDatabase::CleanupTheField($_POST['company_saved'] == sha1("coMpaNySaVed")));

			$this->companies->companies_id = (float)Security::DecodeUrlData($_POST['COMPANIES_ID']);

			if($this->companies->companies_id > 0)
				$this->companies->GetCompanyById();

			if(!$postBack)
			{
				if((int)$_POST['COMPANIES_DELETE'])
				{
					$this->companies->DeleteCompany();
					return;
				}
			}

			if(!$this->isValidPost || $this->isValidPost && !$postBack || $this->isValidPost && $postBack && !$this->ValidateForm())
			{

				if(!$postBack)
				{
					$this->blocksMarkup .= "<input type='hidden' name='company_saved' id='company_saved' value='".sha1("coMpaNySaVed")."'>";
					
					$this->page->commonScripts .= " Objects.Environment['COMPANIES_ID']='".Security::EncodeUrlData($this->companies->companies_id)."'; ";
						//echo $this->companies->companies_id." fff ".Security::DecodeUrlData($_POST['COMPANIES_ID'])." ".$this->companies->isLoaded." ".$this->companies->unique_name_identifier;
					
					$this->blocksMarkup .= "<div id='companiesFields' style='overflow:auto; height:400px; width:580px; '>";
				}
				
				$countriesList = new CountriesList();
				$countriesList->width = '250px';

				$this->blocksMarkup .= "<div class='row'><div><:=Company_Title=:></div><div><input type='text' name='company_name_field' id='company_name_field' value='".$this->companies->name."' title='<:=Company_Title=:>'  />".$formTag->RenderAsterisks($this->fieldsArray['company_name_field'])."</div></div>";
				$this->blocksMarkup .= "<div class='row'><div><:=Company_Unique_Title=:></div><div><input type='text' name='unique_name_field' id='unique_name_field' value='".$this->companies->unique_name_identifier."' title='<:=Company_Unique_Title=:>'  />".$formTag->RenderAsterisks($this->fieldsArray['unique_name_field'])."</div></div>";

				$this->blocksMarkup .= "<div class='row'><div><:=Country_Name=:></div><div>".$countriesList->GetCountriesList($this->companies->country_name)."".$formTag->RenderAsterisks($this->fieldsArray["country_name_field"])."</div></div>";

				$this->blocksMarkup .= "<div class='row'><div><nobr><span id='edrpou'><:=EDRPOU=:></span><span id='reg_number' style='display:none'><:=Registration_Number=:></span></nobr></div><div><input type='text' name='edrpou_field' id='edrpou_field' value='".$this->companies->edrpou_taxpnum."' maxlength='' title='<:=EDRPOU=:>' />".$formTag->RenderAsterisks($this->fieldsArray['edrpou_field'])."</div></div>";
				$this->blocksMarkup .= "<div class='row'><div><:=IBAN_CODE=:></div><div><input  type='text'   name='iban_field'  id='iban_field'     value='".$this->companies->iban."' />".$formTag->RenderAsterisks($this->fieldsArray["iban_field"])."</div></div>";
				$this->blocksMarkup .= "<div class='row'><div><:=Account_Number=:></div><div><input type='text' name='account_number_field' id='account_number_field' value='".$this->companies->account."'>".$formTag->RenderAsterisks($this->fieldsArray['account_number_field'])."</div></div>";

				$this->blocksMarkup .= "<div class='row'><div><:=ISIN_CODE=:></div><div><input  type='text'   name='isin_field'    id='isin_field'     value='".$this->companies->isin."' />".$formTag->RenderAsterisks($this->fieldsArray["isin_field"])."</div></div>";
				$this->blocksMarkup .= "<div class='row'><div><:=VAT_CODE=:></div><div><input  type='text'   name='vat_code_field' id='vat_code_field'  value='".$this->companies->vat_code."' />".$formTag->RenderAsterisks($this->fieldsArray["vat_code_field"])."</div></div>";
				$this->blocksMarkup .= "<div class='row'><div><:=EORI_CODE=:></div><div><input  type='text'   name='eori_field'    id='eori_field'  value='".$this->companies->eori_code."' />".$formTag->RenderAsterisks($this->fieldsArray["eori_field"])."</div></div>";
				$this->blocksMarkup .= "<div class='row'><div><nobr><:=IS_Finance=:></nobr></div><div><input  type='checkbox' name='is_bank_field' id='is_bank_field' value='on' /></div></div>";
				$this->blocksMarkup .= "<div class='row'><div><:=Banks_MFO=:></div><div><input  type='text'   name='mfo_field' 	   id='mfo_field'      value='".$this->companies->mfo."' /></div></div>";
				$this->blocksMarkup .= "<div class='row'><div><:=Banks_Swift=:></div><div><input  type='text'   name='swift_field' id='swift_field'    value='".$this->companies->swift."' maxlength='50' /></div></div>";

				$this->blocksMarkup .= "<div class='row'><div><:=City_Name=:></div><div><input type='hidden' name='cities_id'	   id='cities_id' value='".$this->companies->cities_id."' />";
				$this->blocksMarkup .= "<input type='text' name='city_name_field' id='city_name_field' value='".$this->companies->city_name."' />".$formTag->RenderAsterisks($this->fieldsArray["city_name_field"])."</div></div>";
				$this->blocksMarkup .= "<div class='row'><div><:=Law_ADDRESS=:></div><div><input  type='text'   name='address_field' id='address_field'   value='".$this->companies->address."' />".$formTag->RenderAsterisks($this->fieldsArray["address_field"])."</div></div>";
				$this->blocksMarkup .= "<div class='row'><div><:=Physical_ADDRESS=:></div><div><input  type='text'   name='physical_address_field' id='physical_address_field'   value='".$this->companies->physical_address."' />".$formTag->RenderAsterisks($this->fieldsArray["physical_address_field"])."</div></div>";
				$this->blocksMarkup .= "<div class='row'><div><:=Main_Phone=:></div><div><input type='text' id='phone_main' name='phone_main' value='{$this->companies->phone_main}' /></div></div>";
				$this->blocksMarkup .= "<div class='row'><div><:=Mobile_Phone=:></div><div><input type='text' id='phone_mob' name='phone_mob' value='{$this->companies->phone_mob}' /></div></div>";

				$this->blocksMarkup .= "<div class='row'><div><:=Common_Email=:></div><div><input  type='text'   name='common_email'  id='common_email'  value='".$this->companies->common_email."' />".$formTag->RenderAsterisks($this->fieldsArray["common_email"])."</div></div>";
				$this->blocksMarkup .= "<div class='row'><div><:=Common_WWW=:></div><div><input  type='text'   	 name='common_www'    id='common_www'    value='".$this->companies->common_www."' />".$formTag->RenderAsterisks($this->fieldsArray["common_www"])."</div></div>";
				
				$this->blocksMarkup .= "<div class='row'><div style='vertical-align:top;'><:=Company_Comment=:></div><div><textarea name='company_comment' id='company_comment' cols='30' rows='5' style='width:99%;' >".$this->companies->comment."</textarea></div></div>";

				if(!$postBack)
				{
					$this->blocksMarkup .= "</div>";

					$this->blocksMarkup .= "<div style='text-align: right;'>";
					
					if($this->isAjaxRequest && Request::GetSCode() != $this->SCodes['ajax_savecompany'])	
					{
						$link = new Anchor();
						$link->SCode = $this->SCodes['ajax_savecompany'];

						if(!$this->companies->isLoaded)
						{
							$link->title = $this->GetLocalValue("Company_Create_Link");
						}
						else
						{
							$link->title = $this->GetLocalValue("Company_Save_Link");
						}
						$link->href = Request::$url;
						$link->hrefAJAX = Request::$url; 
						$link->isTraditionalHref = false;
						$link->refreshElementId = 'companiesFields';
						$link->getParamsValues = true;
						$link->applyScripts = false;
						//$link->onClick = "alert(Objects.Environment['COMPANIES_ID']);";
						$link->class = "ajaxLink";
						$link->params = array('company_saved' => '', 'company_name_field' => '', 'edrpou_field' => '', 'unique_name_field' => '', 'country_name_field' => '', 'reg_number' => '', 'account_number_field' => '', 'iban_number' => '', 'vat_code_field' => '', 'eori_field' => '', 'is_bank_field' => '', 
											  'mfo_field' => '', 'swift_field' => '', 'cities_id' => '', 'city_name_field' => '', 'address_field' => '', 'physical_address_field' => '', 'company_comment' => '', 'common_email' => '', 'common_www' => '', 'COMPANIES_ID' => '', "phone_main" => '', "phone_mob" => '');
					
						//$this->blocksMarkup .= "<table style='float:right;'><tr><td colspan='2'>".$link->OpenTag().$link->title.$link->CloseTag()."</td></tr></table>";
						$this->blocksMarkup .= $link->OpenTag().$link->title.$link->CloseTag()."</div>";

						$this->page->commonScripts .= $link->appendClientScript." ";
					}
					else
					{
						$this->blocksMarkup .= "<table><tr><td colspan='2' >".$this->formTag->RenderSubmitButton("<:=Projects_Next_Step=:>")."</td></tr></table></div>";
					}
				}
			}
			else
			{
				$this->blocksMarkup .= "saving";

				// Write company to the Database
				$this->companies->SaveCompany();
				
				if($this->companies->companies_id > 0) 
				{
					$this->blocksMarkup = "Company successfully created!";
					$this->page->commonScripts = "<script>Objects.BubbleDiv.hide();</script>";
				}
				else 
				{
					$this->blocksMarkup = "Error creating the company. Please, try again!";
					
					$this->CreateCompanyForm();
				}
			}

		}
			
		private $isValidForm = false;

		private $companies = NULL;
		
		private $materialsId = 0;
			
		public $blocksMarkup = "";
		
		private function CreateCompanyForm()
		{
			$formTag = new Form();

			$this->pagesMarkup = $formTag->RenderTop();
				
			$hdn = new Hidden();
			$hdn->SetName("__SCode");
			$hdn->SetValue(sha1("AdDinGtHeneWcoMpaNy"));
			
			$countriesList = new CountriesList();
				
			$this->pagesMarkup .= $hdn->OpenTag();
						
			$this->pagesMarkup .= "<table id=''>";

			$this->pagesMarkup .= "<tr><td><:=Company_Title=:></td><td><input type='text' name='company_name_field' value='".$this->companies->name."' title='<:=Company_Title=:>'  />".$formTag->RenderAsterisks($this->fieldsArray['company_name_field'])."</td></tr>";
			$this->pagesMarkup .= "<tr><td><:=Company_Unique_Title=:></td><td><input type='text' name='unique_name_field' value='".$this->companies->unique_name_identifier."' title='<:=Company_Unique_Title=:>'  />".$formTag->RenderAsterisks($this->fieldsArray['unique_name_field'])."</td></tr>";
				
			$this->pagesMarkup .= "<tr><td><:=Country_Name=:></td><td>".$countriesList->GetCountriesList($this->companies->country_name)."".$formTag->RenderAsterisks($this->fieldsArray["country_name_field"])."</td></tr>";

			$this->pagesMarkup .= "<tr><td><span id='edrpou'><:=EDRPOU=:></span><span id='reg_number' style='display:none'><:=Registration_Number=:></span></td><td><input type='text' name='edrpou_field' value='".$this->companies->edrpou_taxpnum."' maxlength='' title='<:=EDRPOU=:>' />".$formTag->RenderAsterisks($this->fieldsArray['edrpou_field'])."</td></tr>";
			$this->pagesMarkup .= "<tr><td><:=IBAN_CODE=:></td><td>         <input  type='text'   name='iban_field'      value='".$this->companies->iban."' />".$formTag->RenderAsterisks($this->fieldsArray["iban_field"])."</td></tr>";
			$this->pagesMarkup .= "<tr style='display:none'><td><:=Account_Number=:></td><td><input type='text' name='account_number_field' value='".$this->companies->account."'>".$formTag->RenderAsterisks($this->fieldsArray['account_number_field'])."</td></tr>";

			$this->pagesMarkup .= "<tr><td><:=ISIN_CODE=:></td><td>         <input  type='text'   name='isin_field'      value='".$this->companies->isin."' />".$formTag->RenderAsterisks($this->fieldsArray["isin_field"])."</td></tr>";
			$this->pagesMarkup .= "<tr><td><:=VAT_CODE=:></td><td>			<input  type='text'   name='vat_code_field'  value='".$this->companies->vat_code."' />".$formTag->RenderAsterisks($this->fieldsArray["vat_code_field"])."</td></tr>";
			$this->pagesMarkup .= "<tr><td><:=EORI_CODE=:></td><td>         <input  type='text'   name='eori_field'      value='".$this->companies->eori_code."' />".$formTag->RenderAsterisks($this->fieldsArray["eori_field"])."</td></tr>";
			$this->pagesMarkup .= "<tr><td><:=IS_Finance=:></td><td>        <input  type='checkbox' name='is_bank_field' value='on' /></td></tr>";
			$this->pagesMarkup .= "<tr><td><:=Banks_MFO=:></td><td>         <input  type='text'   name='mfo_field'       value='".$this->companies->mfo."' /></td></tr>";
			$this->pagesMarkup .= "<tr><td><:=Banks_Swift=:></td><td>       <input  type='text'   name='swift_field'     value='".$this->companies->swift."' maxlength='50' /></td></tr>";

			$this->pagesMarkup .= "<tr><td><:=City_Name=:></td><td><input type='hidden' name='cities_id' value='".$this->companies->cities_id."' />";
			$this->pagesMarkup .= "<input type='text' name='city_name_field' value='".$this->companies->city_name."' />".$formTag->RenderAsterisks($this->fieldsArray["city_name_field"])."</td></tr>";
			$this->pagesMarkup .= "<tr><td><:=Law_ADDRESS=:></td><td>       <input  type='text'   name='address_field'   value='".$this->companies->address."' />".$formTag->RenderAsterisks($this->fieldsArray["address_field"])."</td></tr>";
			$this->pagesMarkup .= "<tr><td><:=Physical_ADDRESS=:></td><td>  <input  type='text'   name='physical_address_field'    value='".$this->companies->physical_address."' />".$formTag->RenderAsterisks($this->fieldsArray["physical_address_field"])."</td></tr>";
				
			$this->pagesMarkup .= "<tr><td><:=Company_Comment=:></td><td><textarea name='company_comment' cols='30' rows='7' >".$this->companies->comment."</textarea></td></tr>";
				
			$this->pagesMarkup .= "<tr><td colspan='2'>";
				
			$this->pagesMarkup .= $formTag->RenderSubmitButton("<:=Add_Company_Button=:>");
				
			$this->pagesMarkup .= "</td></tr></table>";
				
			$this->pagesMarkup .= $formTag->RenderBottom();
		}
		
		private function SearchResultRow($row, $index = 0)
		{
			$this->blocksMarkup .= "<div class='resultsRow'>{$index}.&nbsp;<a href='".Request::GetRoot()."/Companies/".$row['UNIQUE_NAME_IDENTIFIER']."/' >".$row['NAME']."</a><ul companyId='".Security::EncodeUrlData($row['COMPANIES_ID'])."'><li onClick='cDel(this);' title='<:=Company_Row_Delete=:>'>×</li><li onClick='cEdit(this);' title='<:=Company_Row_Edit=:>' class='edit'>…</li></ul></div>";
		}

		private function RenderSearchResults($result, $all = false)
		{
			$companiesCount = $this->companies->RowsCount();

			$local = $this->GetLocalValue('Company_Edit');

			$this->page->commonScripts = '</script><script>function cDel(obj) { if(confirm("'.$this->GetLocalValue("Company_Are_You_Sure_Delete").'") )  EditCompanyClick("'.$local.'", null, 1, obj); Objects.BubbleDiv.hide(); }; function cEdit(obj) { EditCompanyClick("'.$local.'", null, 0, obj); } '.self::InitScripts().'; </script>';

			$this->blocksMarkup .= "<br><div id='searchResults'>";

			$i = 1;
			while($row = mysql_fetch_array($result))
			{
				$this->SearchResultRow($row, $this->companiesOffset + $i++);
			}

			if($all)
			{
				$this->blocksMarkup .= "<div class='searchIndexer'>";

				for($i = 0; $i < ($companiesCount/$this->companiesWindow); $i++)
				{
					$this->blocksMarkup .= "<a href='".Request::GetRoot()."/Companies/?offset=".$i."' ".(((int)$this->companiesOffset/$this->companiesWindow == $i) ? "class='selected'" : "");

					$this->blocksMarkup .= ">".($i + 1)."</a>";
				}

				$this->blocksMarkup .= "</div>";
			}
				
			$this->blocksMarkup .= "</div>";
		}
		
		public function BlockInit()
		{
			$this->companies->unique_name_identifier = Request::$identifier;

			if($this->page->isValidPost && (Request::GetSCode() == $this->SCodes['ajax_profile'] || Request::GetSCode() == $this->SCodes['ajax_savecompany']))
			{
				$this->EditCompanyForm();
				$this->blocksAjaxMarkup .= $this->blocksMarkup;
			}
			elseif($this->page->isValidPost && (Request::GetSCode() == sha1("aJaXaDdAlBum") || Request::GetSCode() == sha1("aJaXCliCkAlbUm")) )
			{
				$this->RenderAlbum((float)Security::DecodeUrlData($_POST['OBJECTS_ID']), (float)Security::DecodeUrlData($_POST['ALBUMS_ID']));
			}
			elseif($this->page->isValidPost && Request::GetSCode() == sha1("mAkeThEpReSeArcHreQuest"))
			{
				$companies = new Companies();

				$result = $companies->LoadCompanies(0, 0, StaticDatabase::CleanupTheField($_POST['companiesInput']));

				//Search container
				if(mysql_num_rows($result) > 0)
				{ 
					while($companies->ParseResult($result))
					{
						$this->blocksAjaxMarkup .= "<div onClick='cSC(this);'>".$row['NAME']."</div>";//"<div onClick='companyClick(\"".Security::EncodeUrlData($row['COMPANIES_ID'])."\", \"".addslashes(StaticDatabase::CleanupTheField($row['NAME']))."\");'>".$row['NAME']."</div>";						
					}
				}
			}
			elseif(strlen($this->companies->unique_name_identifier) < 1)
			{
				$searchText = $this->GetLocalValue("Company_Search_Field");

				$blockSearch = new BlockSearch ();
				$blockSearch->page = $this->page;
				$blockSearch->BlockMode = RENDER_COMPANIES_SEARCH_INPUT;
				$blockSearch->inputId = "companiesInput";
				$blockSearch->inputText = $searchText;
				$blockSearch->divStyle = "display:inline-block;";
				$blockSearch->BlockInit();



				$this->blocks['BLOCK_SEARCH'] = $blockSearch;

				if(!$this->isAjaxRequest)
				{
					$this->page->commonScripts .= "</script><script>".BlockSearch::InitScripts(RENDER_COMPANIES_SEARCH_INPUT)."; function cSC(obj) { if(obj) { $(\"companiesInput\").value=obj.innerHTML; Objects.BubbleDiv.hide(); } }</script>";

				}

				$this->blocksMarkup .= "<div><div >&nbsp;</div><div style=' display:inline-block; '><input type='hidden' id='companiesId' name='companiesId' value=''><nobr><:=BLOCK_SEARCH=:>&nbsp;<button name='searchBtn' id='searchBtn' style='width:120px;' ><:=Company_Search_Btn=:></button></nobr></div></div>";

				// Повертаємо всі компанії
				$this->companiesOffset = ((float)$_GET['offset']) * $this->companiesWindow;

				$result = $this->companies->LoadCompanies($this->companiesWindow, $this->companiesOffset, '', ' `NAME` ');



				$this->RenderSearchResults($result, true);
			}
			elseif($this->companies->unique_name_identifier == "Latest" || $this->companies->unique_name_identifier == "latest")
			{
				$result = $this->companies->LoadCompanies(50, 0, '', ' `UPDATED_TIME` DESC ');
				
				$this->RenderSearchResults($result, false);
			}
			else
			{
				$this->companies->ValidateUniqueIdentifier();
			}

			$albumsId = (float)Security::DecodeUrlData($_POST['ALBUMS_ID']);

			if($this->isValidPost && Request::GetSCode() == sha1("sAveFilEtoDatAbaSe"))
			{
				$this->RenderAlbum($this->companies->companies_id, $albumsId);
			}
			elseif(/*!$this->isValidPost &&*/ $this->companies->isLoaded || $this->isValidPost && (Request::GetSCode() == sha1("ComPanIesProFile") || in_array(Request::GetSCode(), BlockMaterials::GetAllowedSCodes()) ) )
			{ // First time valid loaded from the address bar
				$this->materialId = (float)Security::DecodeUrlData(StaticDatabase::CleanupTheField($_POST['MaterialsId']));
				$this->CompaniesProfile(); 
			}
			elseif(Request::GetSCode() == sha1("EdiTthECompaNyProfiLe")  
					 && $this->isValidPost && $this->isValidForm)
					 
			{
				$this->CompaniesProfileForm();
			}
			elseif(Request::GetSCode() == sha1("ComPanIesProFile") && $this->companies->companies_id > 0)
			{
				$this->CompaniesProfile();
			}
			else
			{
			}
		}	
	}
?>