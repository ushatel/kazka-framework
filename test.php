<!DOCTYPE html>
<html>

<head>

<style>

span                     { content: "span"; }
span::before             { content: "B";    }
span::outside(1)         { display: inline; }
span::outside(1)::before { content: "A";    }
span::outside(2)         { display: inline; }
span::outside(2)::after  { content: "C";    }
</style>
</head>
<body>
<span></span>

<?php 

/*echo GeneratePassword(7,'abczxc012394');

<input type="checkbox" class="form" name="checkbox_2"/>
<input type="checkbox" class="form" name="checkbox_1"/>
<input type="checkbox" class="form" name="checkbox_3"/>
...
<input type="checkbox" class="form" name="checkbox_10"/>
$_POST*/

$arr = array('checkbox_1' => 'false', 'checkbox_3' => 'on');

//$arr = array(34,54,67,68,141,151,161,141,54,151,54);
//GetUniqueOnes($arr) ;
function GetUniqueOnes($arr) 
{
foreach($arr as $key)
{
if(count(array_keys($arr,$key)) == 1)
	echo $key;
}
}


$xmlstr= '<Address><to>James</to><from>Jani</from><heading>Reminder</heading><body>Please check your mail.</body></Address>' ;
//echo ReadXml($xmlstr);

function ReadXml($xmlstr)
{
	$parser = xml_parser_create();
	
	xml_parse_into_struct($parser, $xmlstr, $values, $indexes);
	xml_parser_free($parser);
	
	foreach($values as $val)
	{
		if($val['type'] == 'open')
		{
			echo ucfirst(strtolower($val['tag']))."\r\n";
		}
		elseif($val['type'] == 'complete')
		{
			echo strtolower($val['tag']).": ".ucfirst($val['value'])."\r\n";
		}
	}
}

//echo system ("tracert google.com.ua", $myresult);

//echo "fdsfsdfs ".$myresult;


/*function ReformatPhoneNumber($number)
{
	$num = preg_replace('/([-|\s]{1})(\d)/i', '${2}', $number);
	$size = strlen($num); if(!is_numeric($num) || ($size < 7 || $size > 12 )) { throw new Exception( 'Invalid phone number'); }
	return $num;
} 
function ReformatPhoneNumber($number)
{
     $new_num = preg_replace(array("/\s/", '/-/'), '', $number); 
     $num_size = strlen($new_num);
	 $min_pos = strpos('-', $number);

     if($num_size >= 7 && $num_size <= 12 && (($min_pos > 0) || ($min_pos == false)))
     {
             return $new_num;
     }
     else
     {
     throw new Exception( 'Invalid phone number');
     }

return;
}

//echo ReformatPhoneNumber('012-345 69');

echo ReformatPhoneNumber('01203- 34566')."<br>";

echo "<img src='http://i.work.ua/i/work.ua.gif' style=' border: 1px solid; Filter: FlipV(); background-color:#FF0000;'><h3>Headding</h3>";

echo ReformatPhoneNumber('123456678875432')."<br> ";

echo ReformatPhoneNumber('1234x567')."<br>";
echo "hkjhk kjhkjjjjjjkjkjjh  uuuuuu <mark style='background-color:red;'>llllll</mark> oooooo

<dl>
   <dt>Coffee</dt>
     <dd>Black hot drink</dd>
   <dt>Milk</dt>
     <dd>White cold drink</dd>
 </dl>
<article>
 <h3>Netscape is dead</h3>
 <p><a draggable ='false'  href='http://blog.netscape.com/2007/12/28/end-of-support-for-netscape-web-browsers'>End of support for Netscape web browsers</a>. AOL has a long history on the internet, being one of the first companies to really get people online. Throughout its lifetime, it has been involved with a number of high profile acquisitions, perhaps the largest of which was the 1999 acquisition of the Netscape Communications Corporation. Netscape was known to many as the thought leader in web browsing, and had developed a number of complementary pieces of software that allowed for a rich suite of internet tools.</p>
 </article> 
 
<p>WWF's goal is to: 
<q>build a future where people live in harmony with nature</q>.
 We hope they succeed.</p> 
<ins datetime='2012-02-02'>jhjlkhkjhkjhjjjjjjjjjjjjjjj</ins>
";
*/
//echo "<progress max='100' value='75'></progress>";
/*
'012-345 69'
'01234569'
'012345'
'-012345 678'
'01203- 34566'
'123456678875432'
'1234x567'*/
?>
</body></html>