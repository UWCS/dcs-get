#####################
# dcs-get installer #
#####################
function _dcs-get()
{
if [[ ! -n "$SSH_TTY"  && ! -z "$PS1" ]]
then
        if [[ ! -d /var/tmp/dcs-get || ! -O /var/tmp/dcs-get ]]
        then
                cd /var/tmp
		INSTALL=$(mktemp -p /var/tmp/ dcs-get-install.XXXXXXXXXX) || { echo "Failed to install dcs-get"; exit 1; }
		wget -O $INSTALL -q -T 1 -t 2 http://backus.uwcs.co.uk/dcs-get/dcs-get-install
                if [ $? -eq 0 ]
		then
                	chmod u+x $INSTALL
			$INSTALL "1.3"
			if [ $? -ne 0 ]
			then
				rm $INSTALL
				cd
				return 1
			fi
			rm $INSTALL
		else
			echo "Backus is down, dcs-get is currently unavailiable."
		fi
		cd
        fi
	if [[ -d /var/tmp/dcs-get && -O /var/tmp/dcs-get ]]
	then
        	export LD_LIBRARY_PATH=/var/tmp/dcs-get/lib:/var/tmp/dcs-get/lib64
		export PKG_CONFIG_PATH=/var/tmp/dcs-get/lib/pkgconfig:/var/tmp/dcs-get/lib64/pkgconfig
        	export PATH=/var/tmp/dcs-get/bin:$PATH
#####################
# Add dcs-get install (package)-(version)
# below this comment (and before both 'fi') to install packages on login
#####################
	fi
fi
}

_dcs-get
#####################
# END dcs-get       #
#####################
