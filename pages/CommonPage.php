<?php
	
  if (@preg_match("/includes/i", $_SERVER['PHP_SELF'])) 
    die ("You can't access this file directly...");

	include_once("includes/common/Lib.ILocalizator.php");
	include_once("includes/common/Lib.GlobalLocals.php");
		
	include_once("pages/Block.News.php");
	include_once("pages/Block.Login.php");

/**
 * class CommonPage
 *
 * The main CommonPage
 * 
 * @package Pages.pkg
 */

class CommonPage implements ILocalizator
{
	private $language = "";

	public $pagesItems = NULL; 
	public $pagesMarkup = '';
	public $pagesContent = "";
	public $pagesAjaxMarkup = "";
	public $pagesAjaxObject = NULL;
	public $pagesClientScripts = "";
	
	public $blocks = NULL;
	
	public $request = NULL;
	
	public $localizator = NULL;
	
	public $isHalted = false;
	
	public $isValidPost = false;
	public $isAjaxJSON = false;
	public $isAjaxRequest = false;
	
	public $commonTop = 
	'<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
	<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<:=LANG=:>" lang="<:=LANG=:>">';
	
	public $commonTitle = 'Beta 2 - <:=Title=:>';
	
	public $commonHead = '<script type="text/javascript" src="https://getfirebug.com/firebug-lite.js"></script><script src="/scripts/prototype.js" type="text/javascript"></script><script src="/scripts/custom.js" type="text/javascript"></script>';
	
	public $commonScripts = "";

	public $authMenu = "";
	
	public $commonStyle = '<style type="text/css">
				
	body, span, div, dl, dt, dd, ul, ol, li, h1, h2, h3, h4, h5, h6, pre, code, form, fieldset, legend, input, textarea, p, blockquote, th, td, address {
		margin: 0px;
		padding: 0px;
	}

	body { background: url(\'/images/bg.jpg\') no-repeat fixed ; margin: 0px; padding: 0px; font-family: Tahoma,Arial,Verdana,Geneva,sans-serif; font-size:18px; color:#131313; text-shadow: 0.03em 0.03em 0.03em #DDD; }
	img { border: 0px;}
	
	</style>
  <meta charset="utf-8">
  <meta content="width=1024" name="viewport">
  <meta http-equiv="content-type" content="text/html; charset=utf-8" />

  <meta name="og:image" content="http://myxata.com/images/logo.jpg">
  <meta property="og:site_name" content="MyXata" />
  <meta property="og:type" content="website" />
  <meta property="og:description" content="" />
  
  <link rel="stylesheet" type="text/css" href="/style/style.css" title="style" />
  <!--<link rel="stylesheet" href="/2008/site/css/print" type="text/css" media="print" />-->
  <link rel="shortcut icon" href="/images/favicon16.ico" type="image/x-icon" />

  <meta name="Description" content="Строительный портал. Каталог материалов, проектов и предприятий." />
  <meta name="Keywords" content="building, home, builder, developer, investor, money, industry, строительство, будівництво, myxata, myhata, мухата, myyxata, myyyxata, myyyyxata, муухата, мууухата, мууууухата, maihata, майхата, ремонт, моя хата, мой дом, ваша хата, your hata, your xata, your home, house, нужна хата, ремонт, куплю квартиру, ему хата, балконы, дома, небоскреб, будівництво, строительство" />

  <script type="text/javascript" src="https://apis.google.com/js/plusone.js"></script>
  ';

	private $commonBodyTop = '<div id="grayBlock" class="fix_scroll_wrap fixed" style="opacity:0.7; display:none; height:100%; background-color:#0C3C7A;width:100%" onClick="Objects.BubbleDiv.hide();if(Objects.Album != null) Objects.Album.hide(); "></div>
				  <div id="bubbleDiv" class="fix_scroll_wrap fixed" style="border:1px;left:300px;display:none;height:300px;" ><div style="background-color:#CBEFF9; color:#457988; height: 33px; padding-top: 8px; "><span id="bubbleHeader"></span><div style="float:right; background: url(\'/images/close_button.png\') no-repeat; height: 33px; width:23px; padding-right: 15px; */ margin-top: -22px;" onClick="Objects.BubbleDiv.hide();"></div></div><div id="bubbleContent" style="overflow:auto; height:100%;"></div></div>
				  <div id="bubbleAlbum" class="fix_scroll_wrap fixed" style="border:1px;left:200px;display:none;width: 500px; text-align: center; " ><span><img style="border:1px;" src="/images/spacer.gif" /></span>
				  <div id="scroller" style="position:relative;"><div id="scrollLeft" onClick="Objects.Album.moveLeft();">◄</div><div id="nails"><div id="nailsInner"></div></div><div id="scrollRight" onClick="Objects.Album.moveRight();">►</div></div>
				  </div>

					<div style="background: url(\'/images/top_bg.jpg\'); height: 300px; width: 100%;">
					<div>
					<div id="searchForm"><input type="text" name="searchText" id="searchText" style="background-color:transparent; border: 1px;" value="Search Text" />&nbsp;<button name="searchBtn" id="searchBtn" type="submit" onClick="alert(\'Пошук\');"></button></div>
					<div id="langsLinks"><:=LANGS_MENU=:></div>
					<div id="loginLinks"><:=AUTH_MENU=:></div>
					</div>
					<img src="/images/logo.jpg" style="display:none;" />
					<div style="background: url(\'/images/logo.jpg\'); height: 300px; width: 368px; margin-left:0%; cursor:pointer;" onClick="document.location=\'/\';"></div>
					<div style="background: url(\'/images/mid_top.png\'); height: 65px; width: 1000px; margin-left: 8%; position: relative; top: -160px; color:#FFFFFF; ">
					<div style="padding-top: 18px; height: 44px; padding-left: 10px;" ><nav><:=MENU_TOP=:></nav></div>
					<div class="subMenu"><:=SUB_MENU=:>
					<div id="addthis"><!-- AddThis Button BEGIN -->
						<div class="addthis_toolbox addthis_default_style ">
						<a class="addthis_button_preferred_1"></a>
						<a class="addthis_button_preferred_2"></a>
						<a class="addthis_button_preferred_3"></a>
						<a class="addthis_button_preferred_4"></a>
						<a class="addthis_button_compact"></a>
						<a class="addthis_counter addthis_bubble_style"></a>
						</div>
						<script type="text/javascript" src="http://s7.addthis.com/js/250/addthis_widget.js#pubid=ra-4e200cc9550b2971"></script>
						<!-- AddThis Button END --></div>
					<span id="gplus" style="float:right;padding-top:2px;"><g:plusone size="small" ></g:plusone></span>

					</div></div>

					<div style="top: -115px; position: relative; margin-left:8%; width: 1000px; background-color: #FFFFFF; position: relative; background-position: bottom; display:block; font-size: 17px;">
					<div>

					<div style="background: url(\'/images/right_panel.png\'); left: 730px; overflow: auto; position: absolute; width: 270px; height:100%; ">
					
						<!-- I.UA GIF 1x1 --><script type="text/javascript" language="javascript"><!--
						iS=\'<img src="http://r.i.ua/s?u122071&p0&n\'+Math.random();
						iD=document;if(!iD.cookie)iD.cookie="b=b; path=/";if(iD.cookie)iS+=\'&c1\';
						iS+=\'&d\'+(screen.colorDepth?screen.colorDepth:screen.pixelDepth)
						+"&w"+screen.width+\'&h\'+screen.height;
						iT=iD.referrer.slice(7);iH=window.location.href.slice(7);
						((iI=iT.indexOf(\'/\'))!=-1)?(iT=iT.substring(0,iI)):(iI=iT.length);
						if(iT!=iH.substring(0,iI))iS+=\'&f\'+escape(iD.referrer.slice(7));
						iS+=\'&r\'+escape(iH);
						iD.write(iS+\'" border="0" width="1" height="1" />\');
						//--></script><!-- End of I.UA GIF 1x1 -->

						<!-- I.UA counter image -->
						<a id="iua" href="http://www.i.ua/" target="_blank" onclick="this.href=\'http://i.ua/r.php?122071\';" title="Rated by I.UA">
						<img src="/images/cat_i_ua.png" border="0" /></a><!-- End of I.UA counter image -->

						<!--bigmir)net TOP 100 Part 1-->
						<script type="text/javascript" language="javascript"><!--
						bmN=navigator,bmD=document,bmD.cookie=\'b=b\',i=0,bs=[],bm={o:1,v:16893575,s:16893575,t:6,c:bmD.cookie?1:0,n:Math.round((Math.random()* 1000000)),w:0};
						for(var f=self;f!=f.parent;f=f.parent)bm.w++;
						try{if(bmN.plugins&&bmN.mimeTypes.length&&(x=bmN.plugins[\'Shockwave Flash\']))bm.m=parseInt(x.description.replace(/([a-zA-Z]|\s)+/,\'\'));
						else for(var f=3;f<20;f++)if(eval(\'new ActiveXObject("ShockwaveFlash.ShockwaveFlash.\'+f+\'")\'))bm.m=f}catch(e){;}
						try{bm.y=bmN.javaEnabled()?1:0}catch(e){;}
						try{bmS=screen;bm.v^=bm.d=bmS.colorDepth||bmS.pixelDepth;bm.v^=bm.r=bmS.width}catch(e){;}
						r=bmD.referrer.slice(7);if(r&&r.split(\'/\')[0]!=window.location.host){bm.f=escape(r);bm.v^=r.length}
						bm.v^=window.location.href.length;for(var x in bm) if(/^[ovstcnwmydrf]$/.test(x)) bs[i++]=x+bm[x];
						bmD.write(\'<sc\'+\'ript type="text/javascript" language="javascript" src="http://c.bigmir.net/?\'+bs.join(\'&\')+\'"></sc\'+\'ript>\');
						//-->
						</script>
						<noscript><img src="http://c.bigmir.net/?v16893575&s16893575&t6" width="0" height="0" alt="" title="" border="0" /></noscript>
						<!--bigmir)net TOP 100 Part 1-->

						<!--bigmir)net TOP 100 Part 2-->
						<script type="text/javascript" language="javascript"><!--
						function BM_Draw(oBM_STAT){
							document.write(\'<a id="bigmir" href="http://top.bigmir.net/stat/16893575/" target="_blank"><img src="/images/bigmir2.png" /></a>\');
						}
						//-->
						</script>
						<script type="text/javascript" language="javascript" src="http://c.bigmir.net/?s16893575&t0&l1&o1"></script>
						<noscript>
						<a href="http://top.bigmir.net/stat/16893575/" target="_blank"><img src="http://c.bigmir.net/?v16893575&s16893575&t2&l1" width="88" height="31" alt="bigmir)net TOP 100" title="bigmir)net TOP 100" border="0" /></a>
						</noscript>
						<!--bigmir)net TOP 100 Part 2-->
					
					&nbsp;<div id="invites"><img src="https://ssl.gstatic.com/s2/oz/images/favicon.ico" alt="google+">&nbsp;Google+<a href="https://plus.google.com/_/notifications/ngemlink?path=%2F%3Fgpinv%3D6ffoUnMXoic%3AG16wBl4fSsI" target="_blank">150 invites</a></div>
					&nbsp;<div id="invites" style="margin-top:-15px; margin-left:0px; "><:=Children_Security=:></div>
					
					<div style="border: none; background: none; width: 194px; padding-left:30px;padding-top:10px;"> <div style="width: 192px !important; height:auto!important; border:solid 1px #a9d4ee !important; border-bottom:none!important; background:url(http://s.tchkcdn.com/images/finance/bg_informer_blue.jpg) no-repeat right bottom #afe3f8 !important; font-family:Arial,Helvetica,sans-serif !important;"> <div style="padding:3px 10px 3px !important; border:solid 1px #FFF !important;"> <a href="http://finance.tochka.net/indikatori?a_aid=office@myxata.com&amp;a_bid=00bee45a" class="ft-inf_title_link" style="font-size:16px; text-decoration:none; text-shadow:1px 1px 0px #FFF; color: #006db0 !important;">Курсы валют</a> <i style="font-size:11px !important; font-style:normal !important; text-shadow:1px 1px 0px #FFF !important; color: #006db0 !important;"><script type="text/javascript">(function(){var dt=new Date(),m=(dt.getUTCMonth()+1).toString(),d=dt.getUTCDate().toString();document.write(\'\u043d\u0430 \'+(d.length<2?\'0\'+d:d)+\'.\'+(m.length<2?\'0\'+m:m)+\'.\'+dt.getUTCFullYear());})();</script></i> </div> </div> <iframe src="http://finance.tochka.net/informer/currencyRatesIframe/_ru/__ssi/3/1/18e63c6b7478276d0b37c5d1b6785c75/?type=2&skin=blue&a_aid=office@myxata.com&amp;a_bid=00bee45a" width="194" height="133" frameborder="0" vspace="0" hspace="0" marginwidth="0" marginheight="0" scrolling="no"></iframe></div>
					
					</div>
					
					<div style="display:inline-block; overflow: visible; width: 730px; height: 333px;">
		';	
//						document.write(\'<table cellpadding="0" cellspacing="0" border="0" style="display:inline;margin-right:4px;"><tr><td><div style="margin:0px;padding:0px;font-size:1px;width:88px;"><div style="background:url(\'http://i.bigmir.net/cnt/samples/diagonal/b59_top.gif\') no-repeat bottom;"> </div><div style="font:10px Tahoma;background:url(\'http://i.bigmir.net/cnt/samples/diagonal/b59_center.gif\');"><div style="text-align:center;"><a href="http://www.bigmir.net/" target="_blank" style="color:#0000ab;text-decoration:none;font:10px Tahoma;">bigmir<span style="color:#ff0000;">)</span>net</a></div><div style="margin-top:3px;padding: 0px 6px 0px 6px;color:#003596;"><div style="float:left;font:10px Tahoma;">\'+oBM_STAT.hosts+\'</div><div style="float:right;font:10px Tahoma;">\'+oBM_STAT.hits+\'</div></div><br clear="all"/></div><div style="background:url(\\\'http://i.bigmir.net/cnt/samples/diagonal/b59_bottom.gif\\\') no-repeat top;"> </div></div></td></tr></table>\');
	
	public $commonBottom = 	'</div><footer>
							<div style="background-color: #FFFFFF; height: 80px; position: absolute; width: 1000px;"><div style="padding-top: 15px; padding-left: 15px;">
							<span id="copy"><:=Copy=:><br><a href="http://validator.w3.org/check?uri=referer">HTML5</a> | <a href="http://jigsaw.w3.org/css-validator/check/referer">CSS</a>
							<span id="firefox"><:=Firefox=:></span>
							</span>

							<span id="about" style="display: inline-block; ">
							<nav>
							<span ><a href="/About/"><:=About_Us=:></a></span>
							<span style="padding-left: 7px;"><a href="/Terms/"><:=Terms_Of_Use=:></a></span>
							<span style="padding-left: 7px;"><a href="/Donate/"><:=Donate_Us=:></a></span>
							<span id="gplus"><g:plusone size="small" ></g:plusone></span>
							</nav>
					

							</span>

							<span id="designer" >Дизайн — <a href="http://www.alexey-popov.com/" target="_blank">Алексей Попов</a></span>
							<span id="ukraine"><a href="http://ukraine2012.gov.ua/" target="_blank" ><img src="/images/ukraine_2012.png" height="50" width="75"></a></span></div></div>
							
							<div style="background-color: #1C73B1; height: 5px; width: 1000px; position: absolute;"></div>
							</div></footer>
							</div></div>';
							
	private $vault		=	'</body></html>';

							/*
							<span id="informer">

								<!-- Yandex.Metrika informer -->
								<a href="http://metrika.yandex.ru/stat/?id=7978723&amp;from=informer"
								target="_blank" rel="nofollow"><img src="//bs.yandex.ru/informer/7978723/1_0_C1B8B8FF_A19898FF_1_uniques"
								style="width:80px; height:15px; border:0;" alt="Яндекс.Метрика" title="Яндекс.Метрика: данные за сегодня (уникальные посетители)" /></a>
								<!-- /Yandex.Metrika informer -->

								<!-- Yandex.Metrika counter -->
								<div style="display:none;"><script type="text/javascript">
								(function(w, c) {
									(w[c] = w[c] || []).push(function() {
										try {
											w.yaCounter7978723 = new Ya.Metrika({id:7978723, enableAll: true});
										}
										catch(e) { }
									});
								})(window, "yandex_metrika_callbacks");
								</script></div>
								<script src="//mc.yandex.ru/metrika/watch.js" type="text/javascript" defer="defer"></script>
								<noscript><div><img src="//mc.yandex.ru/watch/7978723" style="position:absolute; left:-9999px;" alt="" /></div></noscript>
								<!-- /Yandex.Metrika counter -->
							</span>

							<span id="liveinternet">
								<!--LiveInternet counter--><script type="text/javascript"><!--
								document.write("<a href=\'http://www.liveinternet.ru/click\' "+
								"target=_blank><img src=\'//counter.yadro.ru/hit?t44.6;r"+
								escape(document.referrer)+((typeof(screen)=="undefined")?"":
								";s"+screen.width+"*"+screen.height+"*"+(screen.colorDepth?
								screen.colorDepth:screen.pixelDepth))+";u"+escape(document.URL)+
								";"+Math.random()+
								"\' alt=\'\' title=\'LiveInternet\' "+
								"border=\'0\' width=\'15\' height=\'15\'><\/a>")
								//--></script><!--/LiveInternet-->

							</span>

							<span id="onlineua">
								<a href="http://www.online.ua/" target="_blank"><img src="http://i.online.ua/catalog/logo/93.png" alt="Украина онлайн" width="15" height="15"></a>
							</span>							
						
							<span id="indexua" style="display:none;">
								<a href="http://index.ua/i/4290/" target="_blank" title="Каталог index.ua">
								<script type="text/javascript">
								<!-- test for fishing!
								document.write(\'<img src="http://stat.index.ua/?stat=4290&r=\'+Math.random()+\'" width="1" height="1" border="0" alt="Каталог index.ua" title="Каталог index.ua" />\');
								//-->
								</script>
								<noscript><img src="http://stat.index.ua/?stat=4290" width="1" height="1" border="0" alt="Каталог index.ua" title="Каталог index.ua" /></noscript></a>
							</span>
*/

	/**
	 * Загальний конструктор 
	 *
	 * Main constructor
	 */
	public function __construct() 
	{
		$this->isValidPost = Request::GetIsValidPost();
		$this->isAjaxRequest = Request::$IsAJAX;
		
	}
	
	public function SetTitle($text)
	{
		if(preg_match("/<:=Title=:>/i", $this->commonTitle))
			$this->commonTitle = preg_replace("/<:=Title=:>/i", $text, $this->commonTitle);
		else
			$this->commonTitle .= $text;
	}
	
	public function NewsInit()
	{
		$news_right = new BlockNews();
		$news_right->page = $this;
		$news_right->BlockMode = RENDER_LATEST_NEWS;
		
		$this->blocks['MENU_NEWS'] = $news_right;
		$this->blocks['MENU_NEWS']->BlockInit();
	}

	public function PageInit() 
	{
	
		if($this->blocks != NULL) 
		{
			foreach($this->blocks as $block)
			{
				if($block != NULL) 
				{
					$block->page = $this;
					$block->BlockInit();
				}
			}
		}
		else 
		{
			
		}
	}
	
	public function FlushTitle() 
	{
		return "<title>".$this->commonTitle."</title>";
	}
	
	public function FlushHead() 
	{
		return "<head>".$this->FlushTitle()."\r\n".$this->commonHead.$this->commonStyle."<script>".$this->commonScripts."</script></head>";
	}
	
	/**
	 * Render the top of the page
	 */	 
	public function FlushTop() 
	{
		return $this->commonTop."\r\n".$this->FlushHead()."<body onload='onLoadInit()' onUnload='onUnload()'>".$this->commonBodyTop;
	}

	/**
	 * Render the main part of the page
	 */	
	public function FlushHTML() 
	{
		$result = "";

		if(!$this->isHalted)
		{
			$result = self::FlushTop().$this->pagesMarkup.self::FlushBottom();
			$result = $this->FlushTopMenu($result);
			$result = $this->FlushLanguage($result);
		}

		return $result;
	}
	
	public function FlushAJAX()
	{
		$result = "";
		
		if(!$this->isHalted)
			$result = $this->pagesAjaxMarkup;
			
		return $result;
	}
	
	public function FlushLanguage($content)
	{
		$langsArray = array("UA", "RU", "EN");
		
		foreach($langsArray as $lang)
		{
			$this->langsMenu .= "<a href=\"/Main/".$lang."/\" ";

			if(strtolower($lang) == Session::GetLang())
			{
				$this->langsMenu .= "class='selected' "; 
			}

			$this->langsMenu .= ">".$lang."</a>";
			
		}
		
		if(($lang = Session::GetLang()) == "ua")
			$lang = "uk";
	
		$result = preg_replace("/<:=LANG=:>/", $lang, $content );
		$result = preg_replace("/<:=LANGS_MENU=:>/", $this->langsMenu, $result );			
		return $result;
	}

	public function FlushTopMenu($content)
	{
		if(Session::GetUserId() > 0)
		{
			$login = Session::GetUserLogin();
			$this->authMenu = "<a href='".Request::GetRoot()."/User/".$login."/' >".$login."</a>&nbsp;<a href='' class='logout' onClick='LoginForm(\"logout\"); return false;'><img src='/images/logout.gif' alt='log out' width='15' height='16' /></a></nobr>";
		}
		else
		{
			$this->authMenu = "<a href=\"/User/Login/\" class=\"ajaxLink\" onClick=\"LoginForm(''); return false;\">".GlobalLocals::GetStaticGlobalValue("Login_Title")."</a>&nbsp;".GlobalLocals::GetStaticGlobalValue("Register_Or")."&nbsp;<a href=\"/User/Registration/\">".GlobalLocals::GetStaticGlobalValue("Register_Title")."</a>";
		}
		$this->authMenu .= "<script>".Login::InitScripts(ALL, '/', 'Login')."</script>";
	
		return preg_replace("/<:=AUTH_MENU=:>/", $this->authMenu , $content);
	}
		
	/**
	 * Збирає нижню частину сторінки
	 */	
	public function FlushBottom () 
	{
		if(Session::IsSuperAdmin())
		{
			$bottom = "<p style='font-size: 7px;'>".Request::ComputeRequestTime().", queries: ".StaticDatabase::GetQueryCount()."</p>";
		}

		$bottom .= "<input type=\"hidden\" id=\"__SVar\" value=\"".Session::$mainSessionVariable."\">".$this->commonBottom.$this->vault;
		return $bottom;
	}
	
	/**
	 * 	Parsing Pages Content from the markup with the pageItems array
	 */
	public function ParsePagesContent() 
	{
		if(!$this->isAjaxRequest)
		{
			$this->pagesContent = self::FlushHTML();
		}
		else
		{
			$this->pagesContent = self::FlushAJAX();
		}

		if(count($this->pagesItems)) 
		{
			foreach( $this->pagesItems as $key => $value ) 
			{
				if(!is_array($value)) 
				{
					$local_value = $this->localizator->locals[$value][Session::GetLang()];
					$this->pagesContent = preg_replace("/<:=".$value."=:>/", $local_value , $this->pagesContent );
				}
			}
		}
	}

	/**
	 * 	Parsing Pages Content from the markup with the pages locals array
	 */
	public function ParsePagesLocalsContent() 
	{
		if(strlen($this->pagesContent) == 0) 
		{
			if(!$this->isAjaxRequest)
				$this->pagesContent = self::FlushHTML();
			else 
			{
				$this->pagesContent = self::FlushAJAX();
			}
		}

		if($this->localizator != NULL)
		{		
			foreach( $this->localizator->locals as $key => $value ) 
			{
				$local_value = $this->localizator->locals[$key][Session::GetLang()];
				$this->pagesContent = preg_replace("/<:=".$key."=:>/", $local_value , $this->pagesContent );
			}
		}
	}
	
	public function ParsePagesGlobalsContent()
	{
		$this->pagesContent = preg_replace("/<:=About_Us=:>/", GlobalLocals::GetStaticGlobalValue("About_Us"), $this->pagesContent);
		$this->pagesContent = preg_replace("/<:=Terms_Of_Use=:>/", GlobalLocals::GetStaticGlobalValue("Terms_Of_Use"), $this->pagesContent);
		$this->pagesContent = preg_replace("/<:=Donate_Us=:>/", GlobalLocals::GetStaticGlobalValue("Donate_Us"), $this->pagesContent);
		$this->pagesContent = preg_replace("/<:=Copy=:>/", GlobalLocals::GetStaticGlobalValue("Copy"), $this->pagesContent);
		$this->pagesContent = preg_replace("/<:=Children_Security=:>/", GlobalLocals::GetStaticGlobalValue("Children_Security"), $this->pagesContent);

		$fox = "";
		if(Request::$browser != Enumerator::$browser['fox'])
			$fox = GlobalLocals::GetStaticGlobalValue("Firefox"); 

		$this->pagesContent = preg_replace("/<:=Firefox=:>/", $fox, $this->pagesContent);
	}
	
	/**
	 * Common Parser
	 */
	public function ParsePage()
	{
		$this->ParsePagesContent();
		$this->ParsePagesLocalsContent();
		$this->ParsePagesGlobalsContent();

		if(count($this->blocks) > 0) 
		{
			// Parse and render blocks content
			foreach($this->blocks as $key => $block)
			{
				$this->pagesContent = preg_replace("/<:=".$key."=:>/i", $block->ParseBlocks(), $this->pagesContent);
			}
		}

		if($this->isAjaxRequest && strlen($this->commonScripts))
		{
			$scripts = $this->commonScripts;
			if(!@preg_match("/script/i", $scripts))
			{
				$scripts = "<script>".$scripts."</script>";
			}
			$this->pagesClientScripts = $scripts;
		}
	}
	
	/**
	 *  Returns the value for the localizable property
	 */
	public function GetLocalValue($property, $lang = "")
	{
		if($lang == "") 
		{
			$lang = Session::GetLang();
		}

		return $this->localizator->locals[$property][$lang];
	}

	/**
	 * Повернути глобалізовану змінну
	 */
	public function GetGlobalValue($property, $lang = "")
	{
		return GlobalLocals::GetStaticGlobalValue($property, $lang);
	}
}

?>