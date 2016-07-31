<?php error_reporting(0); ?>
var auto_refresh = setInterval(
function ()
{
$('#loadInbox').load('<?php echo $_GET['url']; ?>loadinbox.php').fadeIn("slow");}
, 10000); // refresh every 10000 milliseconds
