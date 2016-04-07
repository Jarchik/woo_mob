<?php	 		 	 // custom WordPress database error page tutorial @ digwp.com

	header('HTTP/1.1 503 Service Temporarily Unavailable');
	header('Status: 503 Service Temporarily Unavailable');
	header('Retry-After: 3600'); // 1 hour = 3600 seconds
	mail("admin@emagicone.com", "Database Error", "There is a problem with teh database!", "From: prestashopmanager.com");

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="en" xml:lang="en">
<head>
<title>The site is currently closed for maintenance</title>
<meta http-equiv="content-type" content="text/html; charset=windows-1251" />
<meta name="description" content="The site is currently closed for maintenance" />
<meta name="keywords" content="maintenance page" />
<style>
* {margin: 0;padding: 0;border: 0;}
body {padding: 0;background: #ffffff url(maintenance/page_01.png) repeat-x;color: #000;font-family: Arial;}
#container { padding: 0; background: url(maintenance/page_04.jpg) no-repeat fixed top right; min-height:479px;}
#header {width: 935px; margin: 0 auto auto;}
.imglogo {min-height: 89px; margin: 35px auto auto;}
#content { width: 935px;  min-height: 479px;  margin: auto;}
.img2 {width: 355px;min-height: 479px;margin: 20px auto auto 25px;float: left;}
.text {width: 430px;height: auto;font: 12px arial;color: #000;margin: 90px 25px auto auto;float: right;}
h1 {font: bold 36px arial;margin: 0 auto 18px;}
.success_message {padding: 10px; background: #D0FFD3; border: 1px solid #4CC17F; margin: 5px 0 5px 0;}
.success_message p{margin: 3px 0; font-size: 13px;}
.notice_message {padding: 10px 0;  border-top: 1px solid #ddd; margin: 5px 0 5px 0}
.notice_message p{margin: 3px 0; font-size: 13px;}
.cont_button{
display: block;
margin: 0 auto;
background: url(maintenance/cont_button.jpg) no-repeat 50% 100%;
height: 40px;
width: 176px;
text-decoration: none;
}
.cont_button:hover{
background: url(maintenance/cont_button.jpg) no-repeat 50% 0%;
}
.text_link{
color: #000;
}
.text_link:hover{
text-decoration: none;
}
</style>
<script language=javascript>
function dispDate(dateVal) {
DaystoAdd=dateVal
TodaysDate = new Date();
TodaysDay = new Array('Sunday', 'Monday', 'Tuesday','Wednesday', 'Thursday', 'Friday', 'Saturday');
TodaysMonth = new Array('January', 'February', 'March','April', 'May','June', 'July', 'August', 'September','October', 'November', 'December');
DaysinMonth = new Array('31', '28', '31', '30', '31', '30', '31', '31', '30', '31', '30', '31');
function LeapYearTest (Year) {
if (((Year % 400)==0) || (((Year % 100)!=0) && (Year % 4)==0)) {
return true;
}
else {
return false;
}
}
CurrentYear = TodaysDate.getYear();
if (CurrentYear < 2000) 
CurrentYear = CurrentYear + 1900;
currentMonth = TodaysDate.getMonth();
DayOffset = TodaysDate.getDay();
currentDay = (TodaysDate.getDate())+3;// plus 3 days
month = TodaysMonth[currentMonth];
if (month == 'February') {
if (((CurrentYear % 4)==0) && ((CurrentYear % 100)!=0) || ((CurrentYear % 400)==0)) {
DaysinMonth[1] = 29;
}
else {
DaysinMonth[1] = 28;
}
}
days = DaysinMonth[currentMonth];
currentDay += DaystoAdd;
if (currentDay > days) {
if (currentMonth == 11) {
currentMonth = 0;
month = TodaysMonth[currentMonth];
CurrentYear = CurrentYear + 1
}
else {
month =
TodaysMonth[currentMonth+1];
}
currentDay = currentDay - days;
}
DayOffset += DaystoAdd;
function offsettheDate (offsetCurrentDay) {
if (offsetCurrentDay > 6) {
offsetCurrentDay -= 6;
DayOffset = TodaysDay[offsetCurrentDay-1];
offsettheDate(offsetCurrentDay-1);
}
else {
DayOffset = TodaysDay[offsetCurrentDay];
return true;
}
}
offsettheDate(DayOffset);TheDate  = DayOffset + ', ';
TheDate += month + ' ';
TheDate += currentDay + ', '; 
if (CurrentYear<100) CurrentYear="19" + CurrentYear;
TheDate += CurrentYear;
document.write(' '+TheDate);
}
</script>

<body>
<div id="container" class="some_class">
  <div id="header">                                         
    <img class="imglogo" src="maintenance/page_06.png" />        
  </div>

  <div id="content">  
    <div class="img2">
      <img src="maintenance/page_10.png" /></div>
    <div class="text">
      <h1>The site is currently closed for maintenance</h1>
      <p>eMagicOne Team is improving this site right now to make it better for you. If you have an urgent request, email us at
<a href="mailto: support@emagicone.com "> support@emagicone.com </a></p>
      <br /><br />


 <div class="notice_message">	 <p style="font-size: 16px;">Please visit eMagicOne store <b>as an <a class="text_link" href="//store.emagicone.com" title="eMagicOne eCommerce Store">alternative place</a> </b> to discover our products and services</p><p>To reward your patience, get <b style="color: #d50000;">10% off </b> your next purchase by using coupon code <strong>"EMAG-GD9R-OFF"</strong> <br />
      <small>This offer expires<b><script language=javascript>dispDate(0)</script> </b> </small>
	  <br /><br />
<a class="cont_button" href="//store.emagicone.com/" title="eMagicOne products"> &nbsp; </a>
	  </div>
	  </div>
	</div>
  </div>
</body>
</html>


