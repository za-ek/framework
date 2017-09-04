<!doctype html>
<html lang="ru">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <title>Шаблон za-ek</title>
    <!--[if lt IE 9]>
    <script src="http://html5shiv.googlecode.com/svn/trunk/html5.js"></script>
    <![endif]-->
</head>
<body>
<?php
$this->includeFile($this->pathFromUri($this->conf()->get('request', 'uri')));
?>
</body>
</html>