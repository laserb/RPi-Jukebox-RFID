SETTINGS_PATH=$(awk -F "= *" '/settings_path/ {print $2}' /usr/share/phoniebox/phoniebox.conf)
SHORTCUTS_PATH=$(awk -F "= *" '/shortcuts_path/ {print $2}' /usr/share/phoniebox/phoniebox.conf)
if [ -e $SETTINGS_PATH/global.conf ]
then
    . $SETTINGS_PATH/global.conf
fi
