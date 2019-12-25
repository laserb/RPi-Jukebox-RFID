#!/bin/bash

# Creates a global config file from all the individual
# files in the `settings` folder at:
#    settings/global.conf
# Should be called:
# 1. on startup (list startup sound) to create a latest
#    version of all settings
# 2. each settings change done in the web UI
# 3. a new feature to be implemented: manually triggered
#    in the web UI

# Set the date and time of now
NOW=`date +%Y-%m-%d.%H:%M:%S`

# The absolute path to the folder whjch contains all the scripts.
# Unless you are working with symlinks, leave the following line untouched.
PATHDATA="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
. $PATHDATA/inc.config.sh

#############################################################
# $DEBUG TRUE|FALSE
# Read debug logging configuration file
. $SETTINGS_PATH/debugLogging.conf

# The absolute path to the folder whjch contains all the scripts.
# Unless you are working with symlinks, leave the following line untouched.
PATHDATA="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
if [ "${DEBUG_inc_writeGlobalConfig_sh}" == "TRUE" ]; then echo "########### SCRIPT inc.writeGlobalConf.sh ($NOW) ##" >> $PATHDATA/../logs/debug.log; fi

# create the configuration file from sample - if it does not exist
if [ ! -f $SETTINGS_PATH/rfid_trigger_play.conf ]; then
    cp $SETTINGS_PATH/rfid_trigger_play.conf.sample $SETTINGS_PATH/rfid_trigger_play.conf
    # change the read/write so that later this might also be editable through the web app
    chmod -R 775 $SETTINGS_PATH/rfid_trigger_play.conf
fi

# Path to folder containing audio / streams
# 1. create a default if file does not exist
if [ ! -f $SETTINGS_PATH/Audio_Folders_Path ]; then
    echo "/home/pi/RPi-Jukebox-RFID/shared/audiofolders" > $SETTINGS_PATH/Audio_Folders_Path
    chmod 777 $SETTINGS_PATH/Audio_Folders_Path
fi
# 2. then|or read value from file
AUDIOFOLDERSPATH=`cat $SETTINGS_PATH/Audio_Folders_Path`

# Path to folder containing playlists
# 1. create a default if file does not exist
if [ ! -f $SETTINGS_PATH/Playlists_Folders_Path ]; then
    echo "/home/pi/RPi-Jukebox-RFID/playlists" > $SETTINGS_PATH/Playlists_Folders_Path
    chmod 777 $SETTINGS_PATH/Playlists_Folders_Path
fi
# 2. then|or read value from file
PLAYLISTSFOLDERPATH=`cat $SETTINGS_PATH/Playlists_Folders_Path`

##############################################
# Second swipe
# What happens when the same card is swiped a second time?
# RESTART => start the playlist again vs. PAUSE => toggle pause and play current
# 1. create a default if file does not exist
if [ ! -f $SETTINGS_PATH/Second_Swipe ]; then
    echo "RESTART" > $SETTINGS_PATH/Second_Swipe
    chmod 777 $SETTINGS_PATH/Second_Swipe
fi
# 2. then|or read value from file
SECONDSWIPE=`cat $SETTINGS_PATH/Second_Swipe`

##############################################
# Audio_iFace_Name
# 1. create a default if file does not exist
if [ ! -f $SETTINGS_PATH/Audio_iFace_Name ]; then
    echo "PCM" > $SETTINGS_PATH/Audio_iFace_Name
    chmod 777 $SETTINGS_PATH/Audio_iFace_Name
fi
# 2. then|or read value from file
AUDIOIFACENAME=`cat $SETTINGS_PATH/Audio_iFace_Name`

##############################################
# Audio_Volume_Change_Step
# 1. create a default if file does not exist
if [ ! -f $SETTINGS_PATH/Audio_Volume_Change_Step ]; then
    echo "3" > $SETTINGS_PATH/Audio_Volume_Change_Step
    chmod 777 $SETTINGS_PATH/Audio_Volume_Change_Step
fi
# 2. then|or read value from file
AUDIOVOLCHANGESTEP=`cat $SETTINGS_PATH/Audio_Volume_Change_Step`

##############################################
# Max_Volume_Limit
# 1. create a default if file does not exist
if [ ! -f $SETTINGS_PATH/Max_Volume_Limit ]; then
    echo "100" > $SETTINGS_PATH/Max_Volume_Limit
    chmod 777 $SETTINGS_PATH/Max_Volume_Limit
fi
# 2. then|or read value from file
AUDIOVOLMAXLIMIT=`cat $SETTINGS_PATH/Max_Volume_Limit`

##############################################
# Min_Volume_Limit
# 1. create a default if file does not exist
if [ ! -f $SETTINGS_PATH/Min_Volume_Limit ]; then
    echo "1" > $SETTINGS_PATH/Min_Volume_Limit
    chmod 777 $SETTINGS_PATH/Min_Volume_Limit
fi
# 2. then|or read value from file
AUDIOVOLMINLIMIT=`cat $SETTINGS_PATH/Min_Volume_Limit`

##############################################
# Change_Volume_Idle
# Change volume during idle (or only change it during Play and in the WebApp)
#TRUE=Change Volume during all Time (Default; FALSE=Change Volume only during "Play"; OnlyDown=It is possible to decrease Volume during Idle; OnlyUp=It is possible to increase Volume during Idle
# 1. create a default if file does not exist (set default do TRUE - Volume Change is possible every time)
if [ ! -f $SETTINGS_PATH/Change_Volume_Idle ]; then
    echo "TRUE" > $SETTINGS_PATH/Change_Volume_Idle
fi
# 2. then|or read value from file
VOLCHANGEIDLE=`cat $SETTINGS_PATH/Change_Volume_Idle`

##############################################
# Idle_Time_Before_Shutdown
# 1. create a default if file does not exist
if [ ! -f $SETTINGS_PATH/Idle_Time_Before_Shutdown ]; then
    echo "0" > $SETTINGS_PATH/Idle_Time_Before_Shutdown
    chmod 777 $SETTINGS_PATH/Idle_Time_Before_Shutdown
fi
# 2. then|or read value from file
IDLETIMESHUTDOWN=`cat $SETTINGS_PATH/Idle_Time_Before_Shutdown`

##############################################
# ShowCover
# 1. create a default if file does not exist
if [ ! -f $SETTINGS_PATH/ShowCover ]; then
    echo "ON" > $SETTINGS_PATH/ShowCover
    chmod 777 $SETTINGS_PATH/ShowCover
fi
# 2. then|or read value from file
SHOWCOVER=`cat $SETTINGS_PATH/ShowCover`

##############################################
# edition
# read this always, do not write default

# 1. create a default if file does not exist
#if [ ! -f $SETTINGS_PATH/edition ]; then
#    echo "classic" > $SETTINGS_PATH/edition
#    chmod 777 $SETTINGS_PATH/edition
#fi
# 2. then|or read value from file
chmod 777 $SETTINGS_PATH/edition
EDITION=`cat $SETTINGS_PATH/edition`

##############################################
# Lang
# 1. create a default if file does not exist
if [ ! -f $SETTINGS_PATH/Lang ]; then
    echo "en-UK" > $SETTINGS_PATH/Lang
    chmod 777 $SETTINGS_PATH/Lang
fi
# 2. then|or read value from file
LANG=`cat $SETTINGS_PATH/Lang`

##############################################
# version
# 1. create a default if file does not exist
if [ ! -f $SETTINGS_PATH/version ]; then
    echo "unknown" > $SETTINGS_PATH/version
    chmod 777 $SETTINGS_PATH/version
fi
# 2. then|or read value from file
VERSION=`cat $SETTINGS_PATH/version`

# AUDIOFOLDERSPATH
# PLAYLISTSFOLDERPATH
# SECONDSWIPE
# AUDIOIFACENAME
# AUDIOVOLCHANGESTEP
# AUDIOVOLMAXLIMIT
# AUDIOVOLMINLIMIT
# VOLCHANGEIDLE
# IDLETIMESHUTDOWN
# SHOWCOVER
# EDITION
# LANG
# VERSION

#########################################################
# WRITE CONFIG FILE
rm "$SETTINGS_PATH/global.conf"
echo "AUDIOFOLDERSPATH=\"${AUDIOFOLDERSPATH}\"" >> "$SETTINGS_PATH/global.conf"
echo "PLAYLISTSFOLDERPATH=\"${PLAYLISTSFOLDERPATH}\"" >> "$SETTINGS_PATH/global.conf"
echo "SECONDSWIPE=\"${SECONDSWIPE}\"" >> "$SETTINGS_PATH/global.conf"
echo "AUDIOIFACENAME=\"${AUDIOIFACENAME}\"" >> "$SETTINGS_PATH/global.conf"
echo "AUDIOVOLCHANGESTEP=\"${AUDIOVOLCHANGESTEP}\"" >> "$SETTINGS_PATH/global.conf"
echo "AUDIOVOLMAXLIMIT=\"${AUDIOVOLMAXLIMIT}\"" >> "$SETTINGS_PATH/global.conf"
echo "AUDIOVOLMINLIMIT=\"${AUDIOVOLMINLIMIT}\"" >> "$SETTINGS_PATH/global.conf"
echo "VOLCHANGEIDLE=\"${VOLCHANGEIDLE}\"" >> "$SETTINGS_PATH/global.conf"
echo "IDLETIMESHUTDOWN=\"${IDLETIMESHUTDOWN}\"" >> "$SETTINGS_PATH/global.conf"
echo "SHOWCOVER=\"${SHOWCOVER}\"" >> "$SETTINGS_PATH/global.conf"
echo "EDITION=\"${EDITION}\"" >> "$SETTINGS_PATH/global.conf"
echo "LANG=\"${LANG}\"" >> "$SETTINGS_PATH/global.conf"
echo "VERSION=\"${VERSION}\"" >> "$SETTINGS_PATH/global.conf"

# change the read/write so that later this might also be editable through the web app
chmod -R 775 $SETTINGS_PATH/global.conf
