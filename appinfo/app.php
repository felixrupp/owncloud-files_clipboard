<?php
if(\OC_User::isLoggedIn()) {
	\OCP\Util::addScript('files_clipboard', 'clipboard');
	\OCP\Util::addStyle('files_clipboard', 'clipboard');
} else {
	\OCP\Util::addScript('files_clipboard', 'clearClipboard');
}
?>
