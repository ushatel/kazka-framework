/*  Kazka's JavaScript Prototype Extension, version 0.3.0
 *  (c) 2010 Sysprog
 *
 *  Kazka is freely distributable under the terms of an MIT-style license.
 *  For details, see the Martis UA web site: http://www.martis-ua.com/, http://code.google.com/p/kazka-framework/
 *
 *--------------------------------------------------------------------------*/
var needSecure = true;

function Slide(height, divObjId, closeText, btnObj)
{
	var slider = Objects.Sliders[divObjId];
	if(Objects.Sliders[divObjId] == null)
	{
		slider = new Controls.Slider();
		slider.objectId = divObjId;
		slider.height = height;
		slider.closeText = closeText;
		slider.openText = btnObj.innerHTML;
		slider.button = btnObj;

		Objects.Sliders[divObjId] = slider;
	}
	else
	{
		slider.start();
	}
}

function SButtonClick (inpObject) 
{
	if(inpObject.form)  
	{
		inpObject.form.submit();
	}
}

function SImageClick(obj)
{
	if(Objects.BubbleDiv != null)
	{
		var img = new Image();
		img.src = obj.src;
		Objects.BubbleDiv.content = "<img src='"+obj.src+"' />";
		Objects.BubbleDiv.width = ((window.innerWidth - img.width < 0) ? window.innerWidth - 100 : img.width);
		Objects.BubbleDiv.height = ((window.innerHeight - img.height < 0) ? window.innerHeight - 100 + 33 : img.height);
		Objects.BubbleDiv.title = obj.title;
		Objects.BubbleDiv.show();
	}
}

function SLinkClick (anchorObject, sCode, params) 
{
	var form = null;

	if(anchorObject.parentNode.tagName == "FORM" ) 
	{
		form = anchorObject.parentNode;
	}
	else
	{
		form = document.forms[0];
	}
		
	if(form != null)
	{
	
		for(i = 0; params.length > i; i++)
		{	
			var name_value = params[i].split("=",2);
			if(name_value[0].length > 0 && name_value[1].length > 0)
			{
				var param = document.createElement("INPUT");
				param.name = name_value[0];
				param.value = name_value[1];
				param.type = "hidden";

				form.appendChild(param);
			}
		}
			
		// SCode			
		var param = document.createElement("INPUT");
		param.name = "__SCode";
		param.value = sCode;
		param.type = "hidden";
		form.appendChild(param);
			
		form.submit();

	}
		
	return false;
}

function AjaxLinkClick(anchor)
{	
	var obj = Objects.Links[anchor.id];

	if(obj != null)
	{
		return obj.onClick(anchor);		
	}

	return false;
}

function onLoadInit()  // if not run - client side GUI is not active
{
	SearchInit();

	Security.isBlockedGui = false;
	
	Objects.BubbleDiv = new Controls.BubbleDiv();

	if(needSecure)
	{
		Objects.Security = new Security();
	}

	try 
	{
		if(Objects.OnLoadScripts != null)
		{
			for(i = 0; Objects.OnLoadScripts.length > i; i++)
			{ 
				eval(Objects.OnLoadScripts[i]);
			}
		}
	}
	catch(ex)
	{
		FireError(ex);
	}
}

function SearchInit()
{
	var searchElem = ""; // main search input

	if(Objects.Environment['SearchInput'] != null)
	{
		searchElem = $(Objects.Environment['SearchInput']);
	}
	else
	{
		searchElem = $('searchText');
	}

	if(searchElem)
	{ 
		Objects.Search = new Controls.Search();
		Objects.Search.initialize(searchElem);
		Objects.Search.initObserver();
	}
}

function FireError(ex)
{
	alert(ex.message + " " + ex.description);
}

function onUnload() 
{
	try
	{	
		if(Objects.Upload != null)
		{
			Objects.Upload.destroy();
		}
		
		if(Objects.OnUnloadScripts != null)
		{
			for (i = 0; Objects.OnUnloadScripts.length > i; i++)
			{
				eval(Objects.OnUnloadScripts[i]);
			}
		}
	}
	catch(ex)
	{
		FireError(ex);
	}
}

var Objects = new Array(1);
Objects.Links = new Array(1);
Objects.Sliders = new Array(0);
Objects.Search = null;
Objects.Security = null;
Objects.Upload = null;
Objects.UploadParams = new Array(0);
Objects.Stack = new Array(0);
Objects.Album = null;
Objects.BubbleDiv = null;
Objects.Dictionary = new Array(0);

Objects.Environment = new Array(0);

Objects.OnLoadScripts = new Array();
Objects.OnUnloadScripts = new Array();

var myRequest = Class.create(Ajax.Request, 
	{
	});

var Controls = {};


Controls.BubbleDiv = Class.create(
{
	isShown : false,
	needInitUpload : false,

	width: 100,
	height: 100,

	bubbleDiv: null,
	bubbleContent: null,
	grayBlock: null,
	
	title : "Title",
	
	position: "center", // midtop, center, top
	
	content : '',

	initialize: function ()
	{
		this.bubbleDiv = $('bubbleDiv');
		this.bubbleContent = $('bubbleContent');
		this.bubbleHeader = $('bubbleHeader');
		this.grayBlock = $('grayBlock');
	},

	show: function ()
	{
		
		if(Objects.Upload != null)
		{
			$(Objects.Environment['UploadButtonId']).style.display = 'none';
			this.needInitUpload = true;
		}
	
		if(this.position == "center")
		{			
			var top = (((document.viewport.getHeight()/2) - this.height/2)) + 30;
			var left = (((document.viewport.getWidth()/2) - this.width/2)) + 30;
			
			this.bubbleDiv.style.top = top + 'px';
			this.bubbleDiv.style.left = left + 'px';
		}
		else if(this.position == "midtop")
		{	
			var top = 50;
			var left = (((document.viewport.getWidth()/2) - this.width/2)) + 30;
			
			this.bubbleDiv.style.top = top + 'px';
			this.bubbleDiv.style.left = left + 'px';
		}

		this.bubbleDiv.style.display = 'block';
		this.bubbleDiv.style.height = this.height+'px';
		this.bubbleDiv.style.width = this.width+'px';
		this.bubbleHeader.innerHTML = this.title;
		this.bubbleContent.innerHTML = this.content;
		this.bubbleContent.style.width = this.width-1+'px';
		this.grayBlock.style.display = 'block';

		this.isShown = true;

	},

	refresh: function (contentHtml)
	{	
		if(contentHtml.length > 0)
		{
			this.content = contentHtml;
		}

		this.bubbleContent.innerHTML = this.content;
		this.bubbleHeader.innerHTML = this.title;
	},

	hide: function ()
	{
		this.bubbleDiv.style.display = 'none';
		this.grayBlock.style.display = 'none';

		this.bubbleContent.innerHTML = "";
		this.bubbleHeader.innerHTML = "";
		
		if(Objects.Search != null)
		{
			Objects.Search.hideDiv();
		}

		this.isShown = false;
		
		if(this.needInitUpload)
		{
			$(Objects.Environment['UploadButtonId']).style.display = 'inline-block';
		}
	}
});

	
Controls.Album = Class.create({

	isShown : false,

	isLoaded : false,
	images : new Array(0),

	needInitUpload : false,

	imgWidth : 52,
	width: 500,
	height : 0,

	count : 0,
	
	grayBlock : null,
	bubbleAlbum : null,
	nails : null,
	nailsInner : null,
	scroller : null,
	scrollRight : null,
	scrollLeft : null,
	
	windowPosition : 0,
	windowWidth : 380,
	scrollStep : 35,

	initialize : function ()
	{
		this.grayBlock = $('grayBlock');
		this.bubbleAlbum = $('bubbleAlbum');
		this.nailsInner = $('nailsInner');
		this.scroller = $('scroller');
		this.nails = $('nails');

		this.nails.style.width = this.windowWidth - 50 + "px";
		this.nailsInner.innerHTML = "";
		
		this.scrollRight = $('scrollRight');
		this.scrollLight = $('scrollLeft');
		
		/*
		this.scrollLeft.onclick = this.moveLeft;
		this.scrollRight.onclick = this.moveRight;
		*/

		this.windowPosition = 0; // first image

		this.height = document.viewport.getHeight() - 50;
	},

	show : function ()
	{
		if(Objects.Upload != null)
		{
			$(Objects.Environment['UploadButtonId']).style.display = 'none';
			this.needInitUpload = true;
		}
		var top = (((document.viewport.getHeight()/2) - this.height/2)) ;
		var left = (((document.viewport.getWidth()/2) - this.width/2)) ;

		this.bubbleAlbum.style.width = this.width;
		this.bubbleAlbum.style.top = top + 'px';
		this.bubbleAlbum.style.left = left + 'px';
		
		this.scroller.style.width = this.windowWidth + "px";

		this.grayBlock.style.display = 'block';
		this.bubbleAlbum.style.display = 'block';

		this.isShown = true;
	},
	
	hide : function ()
	{
		if(Objects.Upload != null)
		{
			$(Objects.Environment['UploadButtonId']).style.display = 'inline-block';
			this.needInitUpload = false;
		}
	
		this.bubbleAlbum.style.display = 'none';
		this.grayBlock.style.display = 'none';
	
		this.isShown = false;
	},

	moveLeft: function ()
	{
		if(this.windowPosition - this.scrollStep >= 0)
			this.windowPosition = (this.windowPosition - this.scrollStep);
		else
			this.windowPosition = 0;

		//this.nailsInner.style.left = -(this.windowPosition) + "px";
		this.nailsInner.style.marginLeft = -(this.windowPosition) + "px";

	},

	moveRight: function ()
	{
		if(this.windowPosition + this.scrollStep <= this.imgWidth * this.count)
		{
			this.windowPosition = (this.windowPosition + this.scrollStep);
		}
		else
		{
			this.windowPosition = this.imgWidth * (this.count - 1);
		}

		this.nailsInner.style.marginLeft = -(this.windowPosition) + "px";
	},
	
	imgObjClick : function (obj)
	{
		if(Objects.Album.isShown)
		{
			Objects.Album.bubbleAlbum.firstChild.firstChild.src = obj.src;
			Objects.Album.imgResize(obj);
		}
	},

	imgClick : function ()
	{
		if(Objects.Album.isShown)
		{
			Objects.Album.bubbleAlbum.firstChild.firstChild.src = this.src;
			
			Objects.Album.imgResize(this);
		}
	},
	
	imgResize : function (img)
	{
	
		if(Objects.Album.isShown)
		{
			var left = 0;
			
			if(img.naturalWidth < document.viewport.getWidth() - 200 )
			{													
				if(this.windowWidth > img.naturalWidth)
				{
					left = (document.viewport.getWidth()/2 - this.windowWidth/2);
					this.width = img.naturalWidth;

					Objects.Album.bubbleAlbum.style.width = this.windowWidth + "px";
				}
				else
				{
					left = (document.viewport.getWidth()/2 - this.windowWidth/2);
					this.width = img.naturalWidth;
					Objects.Album.bubbleAlbum.style.width = this.width + "px";
				}
				
				Objects.Album.bubbleAlbum.firstChild.firstChild.width = this.width;
				
			}
			else
			{
				left = (document.viewport.getWidth()/2 - (document.viewport.getWidth()-200)/2);

				if(this.windowWidth < img.naturalWidth)
					this.width = document.viewport.getWidth() - 200;

				Objects.Album.bubbleAlbum.firstChild.firstChild.width = this.width ;
				Objects.Album.bubbleAlbum.style.width = this.width + "px";
			}
			
			Objects.Album.bubbleAlbum.style.marginLeft = left + 'px';
			Objects.Album.bubbleAlbum.style.left = 0 + "px";

			if(img.naturalHeight < document.viewport.getHeight() - 100)
			{	
				Objects.Album.bubbleAlbum.firstChild.firstChild.height = img.naturalHeight ;
			}
			else 
			{
				Objects.Album.bubbleAlbum.firstChild.firstChild.height = document.viewport.getHeight() - 100 ;
			}
			Objects.Album.bubbleAlbum.style.top = ((document.viewport.getHeight()/2) - (this.height - 100)/2) ;
			//this.scroller.style.marginLeft = (this.width/2 - (this.windowWidth)/2) + "px";
			
		}
	},
	
	imgOnLoad : function (img)
	{
		img.src = img.readAttribute("src2");
	},

	preLoad : function (oldBrowser)
	{
		var albums = $$("#albumsPhoto img"); 
  
		this.nailsInner.innerHTML = "";

		this.count = albums.length;
		this.images = new Array(0);
		
		for(var i = 0; i < albums.length; i++) 
		{ 
			var img = new Image(); 
			img.src = albums[i].readAttribute("src2"); 
			img.obj = albums[i];
			img.width = this.imgWidth ;
			img.height = this.imgWidth ;

			if(!oldBrowser)
				img.onload=Objects.Album.imgOnLoad(albums[i]); 
				
			img.onclick=Objects.Album.imgClick; //: function () { Objects.Album.imgClick(this) };
			
			//var img = new Element ( "img", { src: albums[i].readAttribute("src2"), width: this.imgWidth, height: this.imgWidth, onload: Objects.Album.imgOnLoad(albums[i]), onclick: Objects.Album.imgClick });
			
			this.images[i] = img;
			
			this.nailsInner.insert({top : img});
		};

		this.nailsInner.style.width = (this.imgWidth + 1) * this.count + "px";
	}
});

Controls.Search = Class.create(
	{
		requestIsActive : false,
		isHalted : false,
		divSearch : 'div_search',
		grayBlockId : 'grayBlock',
		grayBlock : null,
		
		initialize: function (searchElem)
		{
			this.searchElem = $(searchElem);
			this.previousValue = '';
			this.currentValue = '';
			
			this.grayBlock = $(this.grayBlockId);
		},

		initObserver: function ()
		{
			this.initObserverParam(this.searchElem.name);
		},

		initObserverParam: function (elementName)
		{	
			try {
				this.searchElem.TimedObserver = new Form.Element.Observer(
				  elementName,
				  0.5,  // 500 milliseconds
				  function(el, value) { /// !!! JUST THE SINGLE INSTANCE of Objects.Search YET!!!
				  
				  	Objects.Search.onStateChange(el, value);
				  					    
				  }
				);
			}
			catch(ex)
			{
				FireError(ex);
			}
		},
		
		onStateChange : function(elem, value)
		{	
		    if(this.isHalted == false && !this.requestIsActive && value.length > 2 && Objects.Search.currentValue != value )
		    {
				Objects.Search.previousValue = Objects.Search.currentValue;
				Objects.Search.currentValue = value;
	
		    	if(Objects.Search.previousValue.length > 0 && Objects.Search.previousValue.length < value.length)
		    	{
			    	Objects.Search.searchRequest(Objects.Search.currentValue);
				}
		    }
		    else
		    {
				this.isHalted = false;
		    }
		},
		
		searchRequest : function (searchText)
		{
			var path = ''; //"http://build/test/";

			if(path.length == 0) 
				var path = document.location.href;

			var parameters = { 	
				__SVar  	: Objects.Security.secureServerVar, //$('__SVar').getValue(),
		  		__SCode 	: '9ba6fa92e854aae4a47055f80cde2bca6861e0a4', 
		  		__ClientVar : Objects.Security.createSecureVar(),
		  		IS_AJAX 	: 'TRUE'
		  	}

		  	var param = Objects.Environment['SearchInput'];
		  	parameters[param] = $(param).getValue();

			this.rq = new myRequest(path, {

			parameters: parameters, 

		  	onCreate: function ()
		  	{
		  		this.requestIsActive = true;
		  	},

		  	onFailure: function ()
		  	{
				Objects.Search.hideDiv();

				this.requestIsActive = false;
		  	},

		  	onSuccess: function(response) 
			{
		  		var rObject = Objects.Security.validateResponse(response.responseJSON);

		  		if(rObject.isSecured)
		  		{
					Objects.Search.showDiv(rObject.text);

					try 
					{
				  		if(rObject.scripts.length > 0)
					  		eval(rObject.scripts[0]); 
				  	}
				  	catch(ex)
				  	{
						FireError(ex);
				  	}
				}
				else
				{
					Controls.Search.hideDiv();
				}

				this.requestIsActive = false;
	    	}
	    	});
	    },

		showDiv : function (showText)
		{
			var div = $(this.divSearch).setStyle({ 'display' : 'block' });
			div.style.height = '80px';
			div.style.width = '200px';
			
			this.grayBlock.style.display = 'block';
			this.grayBlock.style.background = " transparent";
			
			div.update(showText);
		},

		hideDiv : function ()
		{
			var div = $(this.divSearch);
			if(div != null)
			{
				div.setStyle({ 'display' : 'none' });
				div.setStyle({ 'heigth' : '0px'});
			}

			this.grayBlock.style.display = 'none';
		}


	});

//$$('#div_search a' // all links in the popup search div

Controls.Slider = Class.create({
	
	objectId 	  : null,
	object 		  : null, 
	timeout 	  : 50, 
	currentHeight : 0,
	stepHeight	  : 5, // px
	height 		  : 0,
	started 	  : false,
	timeoutObj	  : null,
	display		  : "visible",
	openText 	  : "",
	closeText	  : "",
	button		  : null,

	status 		  : false, // true - open, false - closed
	
	initialize : function ()
	{
		
	},
	
	start : function ()
	{	
		if(!this.started)
		{

			this.started = true;
			this.object = $(this.objectId);
			this.refreshTimeout();
		}
		
		if(this.object.style.display == "")
		{
			this.display = "visible";
			this.status = true;
			this.currentHeight = this.height;
		}
	},
	
	refreshTimeout : function()
	{
		this.timeoutObj = setTimeout("Objects.Sliders['"+ this.objectId +"'].plusOne();", this.timeout);	
	},
	
	plusOne : function ()
	{
		if(this.started && (!this.status && this.currentHeight <= this.height) || (this.status && this.currentHeight > 0) )
		{
			this.object.style.height = this.currentHeight + "px";
			this.object.style.display = "";
			this.object.style.visiblity = "visible";
			this.object.style.overflow = "hidden";
			
			
			if(this.status)
			{
				this.currentHeight -= this.stepHeight;
			}
			else
				this.currentHeight += this.stepHeight;
			
			this.refreshTimeout();
		}
		else
		{
			if(this.status)
			{
				this.object.style.display = "none";
				if(this.button != null)
				{
					this.button.innerHTML = this.openText;
				}
			}

			else
			{
				if(this.button != null)
				{
					this.button.innerHTML = this.closeText;
				}
			}

			this.stop();
		}
	},
	
	stop : function()
	{		
		this.started = false;
		this.status = !this.status; // opened/closed
		this.timeoutObj = null;
	}	
});

Link = Class.create({
	
	listId : null,
	sCode : '',

	object : null,
	href : '',
	params : null,
	getParamsValues : true,

	refreshElementId : '',

	requestIsActive : false,


	initialize : function ()
	{
		this.requestIsActive = false; 
	},

	onClick : function (object, elem, sCode)
	{
	
		if(Security.isBlockedGui)
		{
			return ;
		}
		else
		{
			Security.isBlockedGui = true;
		}

		this.object = object; 

		if((this.refreshElementId != '' || elem != null) && !this.requestIsActive)
		{				
			if(this.refreshElementId)
				elem = this.refreshElementId;

			elem = $(elem); // Element should get response
				
			if(this.href.length == 0)	
				this.href = document.location.href;

			if(sCode == null)
				sCode = this.sCode;

			var parameters = {
								__SVar   	: Objects.Security.secureServerVar,
								__SCode  	: sCode,
			  					__ClientVar : Objects.Security.createSecureVar(),
								IS_AJAX  	: 'TRUE'
			  	};
 					  	
			for(var param in this.params)
			{
				try 
				{
					if(this.getParamsValues)
					{
						if(Objects.Environment[param] == null)
						{
							parameters[param] = $(param).getValue();
						}
						else
						{
							parameters[param] = Objects.Environment[param];
						}
					}
					else
						parameters[param] = this.params[param];	

				}
				catch(ex)
				{
					//FireError(ex);
				}
	
			}			

			var rq = new Ajax.Request(this.href, {
	
				parameters : parameters,
				  	
			  	onCreate: function ()
			  	{
			  		this.requestIsActive = true;
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
					  		elem.update(rObject.text);
							
					  		if(rObject.scripts.length > 0)
						  		eval(rObject.scripts[0]); 
						  	

							if(this.successMethod)
								eval(this.successMethod);
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
    		
    		rq = null;
			
			Security.isBlockedGui = false;
		}
	}
	
	});

Controls.SAJAXButton = Class.create({
	
	buttonElement : null,
	requestPath : '',
	sVar : '',
	sCode : '',
	
	initialize : function (buttonElement)
	{
		this.buttonElement = $(buttonElement);
	},
	
	click : function (elem, evt, sCode) 
	{
		if(buttonElement.form)  {

			if(this.requestPath.length == 0)
				var path = document.location.href;

			var rq = new Ajax.Request(path, {
			  	
			  	parameters: { 	__SVar  : inpObject.form.__SVar.value,
			  					__SCode : inpObject.form.__SCode.value,
			  					IS_AJAX : 'TRUE' 
			  		}
		    });
		}
	}
	});


Security = Class.create({
	//hey ho :)
	secureClientVar : 0,
	secureServerVar : '',
	isSecured 		: false,
	isBlockedGui	: true,
	lastResponse 	: null,


	initialize : function()
	{
		this.secureServerVar = $('__SVar').getValue();
	},

	setSecureVar : function (newVar)
	{
		this.secureClientVar = newVar;
	},

	isEqualFloats : function (float1, float2, precision)
	{
		if(precision == null)
			precision = 0.0000001;

		return (float1 + precision >= float1) && (float1 - precision <= float2);
	},

	createSecureVar : function ()
	{
		var rand = Math.random() + new Date().getTime();

		this.setSecureVar(Math.log(rand));
		this.isSecured = false; 
		
		return rand;
	},

	blockGui : function ()
	{
		this.isBlockedGui = true;
	},

	unblockGui : function ()
	{
		this.isBlockedGui = false;
	},

	checkSecureVar : function (valueToCheck)
	{
		var precision = 0.0000001;

		if(this.isEqualFloats(Math.log(valueToCheck), Math.log(this.secureClientVar)) )
		{
			this.isSecured = true;
		}
		else
		{
			this.isSecured = false;
		}

		return this.isSecured;
	},

	validateResponse : function (object) // protects client from agression 
	{
		if(object != null && this.checkSecureVar(object.clientVar) && object.SVar.length < 50)
		{
			if(object.text)
				object.text = object.text.stripScripts();

			if(object.scriptsText)
			{
				object.scripts = object.scriptsText.extractScripts();
			}
			else 
			{
				object.scripts = "";
			}

			object.isSecured = true;

			this.secureServerVar = object.SVar;
			$('__SVar').setValue(this.secureServerVar);

			this.lastResponse = object;
		}
		else
		{
			object = null;
		}		
		
		return object;
	}
});