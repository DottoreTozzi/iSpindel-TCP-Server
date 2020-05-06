#!/bin/bash
echo Updating from GIT...
git pull
echo Installing Server Script...
sudo cp ./iSpindle.py /usr/local/bin/
echo Updating Raspbian Package List...
sudo apt-get update
echo Updating Raspbian Packages...
sudo apt-get dist-upgrade
echo Cleaning Up...
sudo apt-get autoremove
sudo apt-get clean
echo Done.
read -p 'Reboot Now? (y/n): ' -e reboot
if [[ "$reboot" = "y" ]]; then
	sudo shutdown -r now
fi
