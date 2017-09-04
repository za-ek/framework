<!doctype html>
<html lang="ru">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <title><?php
        $this->template()->showFunction('title', function($val) {
            echo ($val) ? $val : 'Шаблон za-ek';
        });
        ?></title>
    <!--[if lt IE 9]>
    <script src="http://html5shiv.googlecode.com/svn/trunk/html5.js"></script>
    <![endif]-->
</head>
<body>
<?php
$this->includeFile($this->route($this->conf()->get('request', 'uri')));
?>
</body>
</html>