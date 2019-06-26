<?php
$eventDispatcher = \OC::$server->getEventDispatcher();
if (\OC_User::isLoggedIn()) {
    $eventDispatcher->addListener('OCA\Files::loadAdditionalScripts', function () {
        OCP\Util::addTranslations('files_clipboard');
        OCP\Util::addScript('files_clipboard', 'clipboard');
        OCP\Util::addStyle('files_clipboard', 'clipboard');
    });
} else {
    OCP\Util::addScript('files_clipboard', 'clearClipboard');
}
