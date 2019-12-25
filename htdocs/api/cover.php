<?php
namespace JukeBox\Api;
include '../config.php';

/*
* debug? Conf file line:
* DEBUG_WebApp_API="TRUE"
*/
$debugLoggingConf = parse_ini_file($conf['settings_path']."/debugLogging.conf");
if($debugLoggingConf['DEBUG_WebApp_API'] == "TRUE") {
    file_put_contents("../../logs/debug.log", "\n# WebApp API # " . __FILE__ , FILE_APPEND | LOCK_EX);
    file_put_contents("../../logs/debug.log", "\n  # \$_SERVER['REQUEST_METHOD']: " . $_SERVER['REQUEST_METHOD'] , FILE_APPEND | LOCK_EX);
}

/**
 * Returns the cover of the currently played folder.
 */

$Audio_Folders_Path = trim(file_get_contents($conf['settings_path'].'/Audio_Folders_Path'));
$Latest_Folder_Played = trim(file_get_contents($conf['settings_path'].'/Latest_Folder_Played'));

$spover = $conf['settings_path']."/cover.jpg";
$ocover = $Audio_Folders_Path."/".$Latest_Folder_Played."/cover.jpg";
$nocover = "../_assets/img/No_Cover.jpg";

if(file_exists($Audio_Folders_Path.'/'.$Latest_Folder_Played.'/cover.jpg')) {
    $filename = $ocover;
} elseif (file_exists($Audio_Folders_Path.'/'.$Latest_Folder_Played.'/spotify.txt')) {
    $filename = $spover;
} else {
    $filename = $nocover;
}

header("Content-Type: image/jpeg");
header("Content-Length: " . filesize($filename));
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

$fp = fopen($filename, 'rb');
fpassthru($fp);

?>
