<?php 

if (file_exists("../../libs")) {
	set_include_path(get_include_path() . ':../../libs');
}
if (file_exists("../libs")) {
	set_include_path(get_include_path() . ':../libs');
}
if (file_exists("../includes/PHPExcel/Classes")) {
	set_include_path(get_include_path() . ':../includes/PHPExcel/Classes');

}
if (file_exists("includes/PHPExcel/Classes")) {
	set_include_path(get_include_path() . ':includes/PHPExcel/Classes');
}
if (file_exists("includes/jpgraph-3.5.0b1/src")) {
	set_include_path(get_include_path() . ':includes/jpgraph-3.5.0b1/src');
}
if (file_exists("../includes/jpgraph-3.5.0b1/src")) {
        set_include_path(get_include_path() . ':../includes/jpgraph-3.5.0b1/src');
}

if (file_exists("../../conf/settings.inc.php")) {
	require_once '../../conf/settings.inc.php';
}
if (file_exists("../conf/settings.inc.php")) {
	require_once '../conf/settings.inc.php';
}

function my_autoloader($class_name) {
        if(file_exists("../../libs/" . $class_name . ".class.inc.php")) {
                require_once $class_name . '.class.inc.php';
        }
	elseif (file_exists("../libs/" . $class_name . ".class.inc.php")) {
                require_once $class_name . '.class.inc.php';
        }

}

spl_autoload_register('my_autoloader');

date_default_timezone_set(__TIMEZONE__);
$db = new db(__MYSQL_HOST__,__MYSQL_DATABASE__,__MYSQL_USER__,__MYSQL_PASSWORD__);

?>
