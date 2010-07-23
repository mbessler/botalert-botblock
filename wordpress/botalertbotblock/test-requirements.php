<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-trans
itional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title>BotAlert/BotBlock PHP Requirements Test</title>
<style type="text/css" media="all">
body
{
        font-family:sans-serif, monospace;
}

</style>
</head>

<body>
<H2>BotAlert/BotBlock PHP Requirements Test</H2>
<P><H3><A HREF="http://php.net/manual/en/book.curl.php">PHP cURL support</A>: 
<?php
    if(!function_exists("curl_init"))
    {
        error_log("CURL not AVAILABLE. Please install PHP-cURL");
        echo '<font color="red">FAIL</font>';
    }
    else 
    {
        echo '<font color="green">PASS</font>';
    }
?></H3>
</P>
</body>
</html>
