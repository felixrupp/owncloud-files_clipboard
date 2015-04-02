<?php
if(\OC_User::isLoggedIn()) {
		if(OCP\Util::getVersion()[0] >= 8) { 
				OCP\Util::addTranslations('files_clipboard');
		}
		OCP\Util::addScript('files_clipboard', 'clipboard');
		OCP\Util::addStyle('files_clipboard', 'clipboard');
} else {
		OCP\Util::addScript('files_clipboard', 'clearClipboard');
}