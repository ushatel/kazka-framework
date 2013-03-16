<meta name="keywords" content="" />
<meta name="description" content="" />


<?
echo '<script src="/scripts/prototype.js"></script>

<script>';
// !!! This theoreticaly closes clicker attack !!! + Search bots for the inner page

echo '
	function SButtonClick (inpObject) 
	{
		if(inpObject.form)  {
			inpObject.form.submit();
		}
	}

	function SLinkClick (anchorObject, sCode, params[] = NULL) 
	{
		if(anchorObject.parentNode.tagName == "FORM") 
		{
			foreach(param in params)
			{
				var name_value = param.split("=",2);
				if(name_value[0].length > 0 && name_value[1].length > 0)
				{
					var param = document.createElement("INPUT");
					param.name = name_value[0];
					param.value = name_value[1];
					param.type = "hidden";
					
					anchorObject.parentNode.appendChild(param);
				}
			}
			
			anchorObject.parentNode.__SCode.value = sCode;
			anchorObject.parentNode.submit();
		}
		
		return false;
	}	
	
	function SAJAXButtonClick (inpObject) 
	{
		if(inpObject.form)  {
			alert(document.location.href);
			var path = document.location.href;//((inpObject.form.action == \'\') ? document.location : inpObject.form.action)	;

			new Ajax.Request(path, {
			  parameters: { __SVar  : inpObject.form.__SVar.value,
			  				__SCode : inpObject.form.__SCode.value,
			  				IS_AJAX : \'TRUE\' /*$F("\'"+inpObject.form.__SCode.value + "\'")*/}
			  				/*, */
/*			  onSuccess: function(response) {
			  	alert("abrakadabra, love :)")

			    }
*/		
		    })

//			inpObject.form.submit();
		}
	}
	
;

</script>';

?> 