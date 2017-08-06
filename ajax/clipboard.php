<?php
OCP\JSON::checkLoggedIn();
OCP\JSON::callCheck();

$l = \OC::$server->getL10N('files_clipboard');

$user = OCP\User::getUser();
$view = new \OC\Files\View();

$required = array('operation', 'directory', 'files', 'destination');
if(count(array_intersect_key(array_flip($required), $_POST)) !== count($required)) {
	OCP\JSON::error();
	exit();
}

$messages = array();
$cut = $_POST['operation'] == 'cut';
foreach($_POST['files'] as $file) {
	$source = $user . "/files/" . \OC\Files\Filesystem::normalizePath(stripslashes($_POST['directory']) . '/' . $file);
	$target = $user . "/files" . \OC\Files\Filesystem::normalizePath(stripslashes($_POST['destination']) . '/' . $file);

	if (!$view->file_exists($source)) {
		$messages[] = $l->t("Unable to paste '%s' item does not exists", array($file));
		continue;
	}

	if (strpos($target, $source) === 0) {
		if ($cut) {
			$messages[] = $l->t("Unable to move folder '%s' into itself", array($file));
		} else {
			$messages[] = $l->t("Unable to copy folder '%s' into itself", array($file));
		}
		continue;
	}

	if ($view->file_exists($target)) {
		if (!$view->unlink($target)) {
			$messages[] = $l->t("Could not remove '%s'", array($file));
			continue;
		}
	}

	if ($cut) {
		if (!$view->rename($source, $target)) {
			$messages[] = $l->t("Could not move '%s'", array($file));
		}
	} else {
		if (!$view->copy($source, $target)) {
			$messages[] = $l->t("Could not copy '%s'", array($file));
		}
	}
}

if (empty($messages)) OCP\JSON::success();
else OCP\JSON::error(array("messages" => $messages));
