<?php
/**************************************************
* VARIABLES
* No changes required if you stuck to the
* INSTALL-stretch.md instructions.
* If you want to change the paths, edit config.php
***************************************************/

include("config.php");

/*
* Config for debug logging
* this file is read by shell scripts and php
*/
$debugAvail = array(
"DEBUG_WebApp",
"DEBUG_WebApp_API",
"DEBUG_inc_readArgsFromCommandLine_sh",
"DEBUG_inc_settingsFolderSpecific_sh",
"DEBUG_inc_writeFolderConfig_sh",
"DEBUG_inc_writeGlobalConfig_sh",
"DEBUG_playlist_recursive_by_folder_php",
"DEBUG_playout_controls_sh",
"DEBUG_resume_play_sh",
"DEBUG_rfid_trigger_play_sh",
"DEBUG_shuffle_play_sh",
"DEBUG_single_play_sh",
);
$debugOptions = array("TRUE", "FALSE");

if(!file_exists($conf['settings_path']."/debugLogging.conf")) {
    // create file
    $debugLoggingConf = "";
    foreach($debugAvail as $debugItem) {
        $debugLoggingConf .= $debugItem."=\"FALSE\"\n";
    }
    file_put_contents($conf['settings_path']."/debugLogging.conf", $debugLoggingConf);
}
// read file
$debugLoggingConf = parse_ini_file($conf['settings_path']."/debugLogging.conf");
/*
* DEBUGGING
* for debugging, set following var to true.
* This will only print the executable strings, not execute them
*/
if($debugLoggingConf['DEBUG_WebApp'] == "TRUE") {
    $debug = "true"; // true or false
}



$protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') ? 'https://' : 'http://';
$url_abs = $protocol.$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF']; // URL to PHP_SELF

/**
 * @param $exec
 */
function execAndRedirect($exec)
{
    global $debug;
    global $url_abs;

    if (!isset($exec)) {
        return;
    }

    if ($debug == "true") {
        print "Command in execAndRedirect: " . $exec;
    } else {
        $res = exec($exec);
        //if ($debug == "true") {
            print "Command in execAndRedirect: " . $exec;
            print "Result: " . $res;
        //}
        /* redirect to drop all the url parameters */
        header("Location: " . $url_abs);
        exit;
    }
}

function fileGetContentOrDefault($filename, $defaultValue)
{
    return file_exists($filename) ? trim(file_get_contents($filename)) : $defaultValue;
}

include("func.php");

// path to script folder from github repo on RPi
$conf['scripts_abs'] = realpath(getcwd().'/../scripts/');
// path to shared folder from github repo on RPi
$conf['shared_abs'] = realpath(getcwd().'/../shared/');

/*
* Vars from the settings folder
*/
if(!file_exists($conf['settings_path']."/global.conf")) {
    // execute shell to create config file
    // scripts/inc.writeGlobalConfig.sh
    exec($conf['scripts_abs']."/inc.writeGlobalConfig.sh");
    exec("chmod 777 ".$conf['settings_path']."/global.conf");
}

// read the global conf file
$globalConf = parse_ini_file($conf['settings_path']."/global.conf", $process_sections = null);
//print "<pre>"; print_r($globalConf); print "</pre>"; //???

// assign the values from the global conf file to the vars in PHP
$Audio_Folders_Path = $globalConf['AUDIOFOLDERSPATH'];
$Second_Swipe = $globalConf['SECONDSWIPE'];
$ShowCover = $globalConf['SHOWCOVER'];
$version = $globalConf['VERSION'];
$edition = $globalConf['EDITION'];
$maxvolumevalue = $globalConf['AUDIOVOLMAXLIMIT'];
$conf['settings_lang'] = $globalConf['LANG'];

// vars that must be read continuously and can't be in the global conf file
$Latest_Folder_Played = trim(file_get_contents($conf['settings_path'].'/Latest_Folder_Played'));

/*
* load language strings
*/
include("inc.langLoad.php");

/*******************************************
* URLPARAMETERS
*******************************************/

$urlparams = array();
/*
* Firstly, collect via 'GET', later collect 'POST'
*/
$nonEmptyCommands = array(
    'play',
    'playpos',
    'player',
    'stop',
    'volume',
    'maxvolume',
    'volstep',
    'shutdown',
    'reboot',
    'scan',
    'idletime',
    'shutdownafter',
    'stopplayoutafter',
    'enableresume',
    'disableresume',
    'enableshuffle',
    'disableshuffle',
    'singleenable',
    'singledisable',
    'DebugLogClear',
);
foreach ($nonEmptyCommands as $command) {
    if(isset($_GET[$command]) && trim($_GET[$command]) != "") {
        $urlparams[$command] = trim($_GET[$command]);
    }
    if(isset($_POST[$command]) && trim($_POST[$command]) != "") {
        $urlparams[$command] = trim($_POST[$command]);
    }
}

$commandsWithAllowedValues = array(
    'recursive' => array('true'),
    'mute' => array('true'),
    'volumeup' => array('true'),
    'volumedown' => array('true'),
    'rfidstatus' => array('turnon', 'turnoff'),
    'gpiostatus' => array('turnon', 'turnoff'),
);
foreach ($commandsWithAllowedValues as $command => $allowedValues) {
    if(isset($_GET[$command]) && in_array(trim($_GET[$command]), $allowedValues)) {
        $urlparams[$command] = trim($_GET[$command]);
    }
    if(isset($_POST[$command]) && in_array(trim($_POST[$command]), $allowedValues)) {
        $urlparams[$command] = trim($_POST[$command]);
    }
}

if ($debug == "true") {
    print "urlparams: ";
    print "<pre>"; print_r($urlparams); print "</pre>";
}

/*******************************************
* URLPARAMETERS cardEdit.php and cardRegisterNew.php
*******************************************/
if(isset($_POST['cardID']) && $_POST['cardID'] != "") { // && file_exists('../shared/shortcuts/'.$_POST['cardID'])) {
    $post['cardID'] = $_POST['cardID'];
} elseif(isset($_GET['cardID']) && $_GET['cardID'] != "") {
    $post['cardID'] = $_GET['cardID'];
}
if(isset($_POST['streamURL']) && $_POST['streamURL'] != "") {
    $post['streamURL'] = $_POST['streamURL'];
}
if(isset($_POST['streamFolderName']) && $_POST['streamFolderName'] != "") {
    $post['streamFolderName'] = $_POST['streamFolderName'];
}
if(isset($_POST['streamType']) && $_POST['streamType'] != "" && $_POST['streamType'] != "false") {
    $post['streamType'] = $_POST['streamType'];
}
if(isset($_POST['audiofolder']) && $_POST['audiofolder'] != "" && $_POST['audiofolder'] != "false" && file_exists($Audio_Folders_Path.'/'.$_POST['audiofolder'])) {
    $post['audiofolder'] = $_POST['audiofolder'];
}
if(isset($_POST['YTstreamURL']) && $_POST['YTstreamURL'] != "") {
    $post['YTstreamURL'] = $_POST['YTstreamURL'];
}
if(isset($_POST['YTstreamFolderName']) && $_POST['YTstreamFolderName'] != "") {
    $post['YTstreamFolderName'] = $_POST['YTstreamFolderName'];
}
if(isset($_POST['YTaudiofolder']) && $_POST['YTaudiofolder'] != "" && $_POST['YTaudiofolder'] != "false" && file_exists($Audio_Folders_Path.'/'.$_POST['YTaudiofolder'])) {
    $post['YTaudiofolder'] = $_POST['YTaudiofolder'];
}
if(isset($_POST['submit']) && $_POST['submit'] == "submit") {
    $post['submit'] = $_POST['submit'];
}
if(isset($_POST['delete']) && $_POST['delete'] == "delete") {
    $post['delete'] = $_POST['delete'];
}
if(isset($_GET['delete']) && $_GET['delete'] == "delete") {
    $post['delete'] = $_GET['delete'];
}


/*******************************************
* ACTIONS
*******************************************/

$commandToAction = array(
    'volume' => $conf['scripts_abs']."/playout_controls.sh -c=setvolume -v=%s",            // change volume
    'maxvolume' => $conf['scripts_abs']."/playout_controls.sh -c=setmaxvolume -v=%s",      // change max volume
    'volstep' => $conf['scripts_abs']."/playout_controls.sh -c=setvolstep -v=%s",          // change volume step
    'mute' => $conf['scripts_abs']."/playout_controls.sh -c=mute",                         // volume mute (toggle)
    'volumeup' => $conf['scripts_abs']."/playout_controls.sh -c=volumeup",                 // volume up
    'volumedown' => $conf['scripts_abs']."/playout_controls.sh -c=volumedown",             // volume down
    'idletime' => $conf['scripts_abs']."/playout_controls.sh -c=setidletime -v=%s",        // set idletime
    'shutdownafter' => $conf['scripts_abs']."/playout_controls.sh -c=shutdownafter -v=%s", // set shutdownafter time (sleeptimer)
    'stopplayoutafter' => $conf['scripts_abs']."/playout_controls.sh -c=playerstopafter -v=%s",// set playerstopafter time (auto stop timer)
    'playpos' => $conf['scripts_abs']."/playout_controls.sh -c=playerplay -v=%s",          // play from playlist position,
    'DebugLogClear' => "rm ../logs/debug.log; touch ../logs/debug.log; chmod 777 ../logs/debug.log",
    'scan' => array(
        'true' => $conf['scripts_abs']."/playout_controls.sh -c=scan > /dev/null 2>&1 &"   // scan the library
    ),
    'stop' => array(
        'true' => $conf['scripts_abs']."/playout_controls.sh -c=playerstop"                // stop playing
    ),
    'reboot' => array(
        'true' => $conf['scripts_abs']."/playout_controls.sh -c=reboot > /dev/null 2>&1 &" // reboot the jukebox
    ),
    'shutdown' => array(
        'true' => $conf['scripts_abs']."/playout_controls.sh -c=shutdown > /dev/null 2>&1 &"// shutdown the jukebox
    ),
    'rfidstatus' => array(
        'turnon' => "/usr/bin/systemctl start phoniebox-rfid-reader.service",                     // start the rfid service
        'turnoff' => "/usr/bin/systemctl stop phoniebox-rfid-reader.service"                      // stop the rfid service
    ),
    'gpiostatus' => array(
        'turnon' => "/usr/bin/systemctl start phoniebox-gpio-buttons.service",                    // start the gpio button service
        'turnoff' => "/usr/bin/systemctl stop phoniebox-gpio-buttons.service"                     // stop the gpio button service
    ),
    // control player through web interface
    'player' => array(
        "next" => $conf['scripts_abs'] . "/playout_controls.sh -c=playernext",
        "prev" => $conf['scripts_abs'] . "/playout_controls.sh -c=playerprev",
        "play" => $conf['scripts_abs'] . "/playout_controls.sh -c=playerplay",
        "replay" => $conf['scripts_abs'] . "/playout_controls.sh -c=playerreplay",
        "pause" => $conf['scripts_abs'] . "/playout_controls.sh -c=playerpause",
        "repeat" => $conf['scripts_abs'] . "/playout_controls.sh -c=playerrepeat -v=playlist",
        "single" => $conf['scripts_abs'] . "/playout_controls.sh -c=playerrepeat -v=single",
        "repeatoff" => $conf['scripts_abs'] . "/playout_controls.sh -c=playerrepeat -v=off",
        "seekBack" => $conf['scripts_abs'] . "/playout_controls.sh -c=playerseek -v=-15",
        "seekAhead" => $conf['scripts_abs'] . "/playout_controls.sh -c=playerseek -v=+15",
    ),
);
foreach ($urlparams as $paramKey => $paramValue) {
    if(isset($commandToAction[$paramKey]) && !is_array($commandToAction[$paramKey])) {
        $exec = sprintf($commandToAction[$paramKey], $paramValue);
        execAndRedirect($exec);
    } elseif (isset($commandToAction[$paramKey]) && isset($commandToAction[$paramKey][$paramValue])) {
        $exec = sprintf($commandToAction[$paramKey][$paramValue], $paramValue);
        execAndRedirect($exec);
    }
}

// enable resume
if(isset($urlparams['enableresume']) && $urlparams['enableresume'] != "" && is_dir(urldecode($Audio_Folders_Path."/".$urlparams['enableresume']))) {
    $exec = $conf['scripts_abs'].'/resume_play.sh -c=enableresume -d="'.$urlparams['enableresume'].'"';
    if($debug == "true") {
        print "Command: ".$exec;
    } else {
        // pass folder to resume script
        exec($exec);

        /* redirect to drop all the url parameters */
        header("Location: ".$url_abs);
        exit;
    }
}

// disable resume
if(isset($urlparams['disableresume']) && $urlparams['disableresume'] != "" && is_dir($Audio_Folders_Path."/".urldecode($urlparams['disableresume']))) {
    // pass folder to resume script
    $exec = $conf['scripts_abs'].'/resume_play.sh -c=disableresume -d="'.$urlparams['disableresume'].'"';
    execAndRedirect($exec);
}

// enable shuffle
if(isset($urlparams['enableshuffle']) && $urlparams['enableshuffle'] != "" && is_dir(urldecode($Audio_Folders_Path."/".$urlparams['enableshuffle']))) {
    // pass folder to resume script
    $exec = $conf['scripts_abs'].'/shuffle_play.sh -c=enableshuffle -d="'.$urlparams['enableshuffle'].'"';
    execAndRedirect($exec);
}

// disable shuffle
if(isset($urlparams['disableshuffle']) && $urlparams['disableshuffle'] != "" && is_dir(urldecode($Audio_Folders_Path."/".$urlparams['disableshuffle']))) {
    // pass folder to resume script
    $exec = $conf['scripts_abs'].'/shuffle_play.sh -c=disableshuffle -d="'.$urlparams['disableshuffle'].'"';
    execAndRedirect($exec);
}

// enable single track play
if(isset($urlparams['singleenable']) && $urlparams['singleenable'] != "" && is_dir(urldecode($Audio_Folders_Path."/".$urlparams['singleenable']))) {
    // pass folder to single_play script
    $exec = $conf['scripts_abs'].'/single_play.sh -c=singleenable -d="'.$urlparams['singleenable'].'"';
    execAndRedirect($exec);
}

// disable single track play
if(isset($urlparams['singledisable']) && $urlparams['singledisable'] != "" && is_dir(urldecode($Audio_Folders_Path."/".$urlparams['singledisable']))) {
    // pass folder to single_play script
    $exec = $conf['scripts_abs'].'/single_play.sh -c=singledisable -d="'.$urlparams['singledisable'].'"';
    execAndRedirect($exec);
}

// play folder audio files
if(isset($urlparams['play']) && $urlparams['play'] != "" && is_dir(urldecode($Audio_Folders_Path."/".$urlparams['play']))) {
    // pass folder to playout script
    $exec = $conf['scripts_abs'].'/rfid_trigger_play.sh -d="'.$urlparams['play'].'"';
    if($urlparams['recursive'] == "true") {
        $exec .= ' -v="recursive"';
    }
    execAndRedirect($exec);
}
?>
