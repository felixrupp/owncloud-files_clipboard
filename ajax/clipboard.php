<?php
OCP\JSON::checkLoggedIn();
OCP\JSON::callCheck();

$l = \OC::$server->getL10N('files_clipboard');

$required = array('operation', 'directory', 'files', 'destination');
if(count(array_intersect_key(array_flip($required), $_POST)) !== count($required)) {
	OCP\JSON::error();
	exit();
}

function copyr($src, $dest){
	if (!\OC\Files\Filesystem::is_dir($src)) {
		return \OC\Files\Filesystem::copy($src, $dest);
	} else {
		if (($dh = \OC\Files\Filesystem::opendir($src)) !== false) {
			if (!\OC\Files\Filesystem::mkdir($dest)) return false;
			while (($file = readdir($dh)) !== false) {
				if ($file == "." || $file == "..") continue;
				if (!copyr($src.'/'.$file, $dest.'/'.$file)) return false;
			}
		}
		return true;
	}
}

function unlinkr($dir){
	if (!\OC\Files\Filesystem::is_dir($dir)) \OC\Files\Filesystem::unlink($dir);
	else {
		$dh = \OC\Files\Filesystem::opendir($dir);
		while (($file = readdir($dh)) !== false) {
			if ($file == "." || $file == "..") continue;
			unlinkr($dir.'/'.$file);
		}
		\OC\Files\Filesystem::rmdir($dir);
	}
	return !\OC\Files\Filesystem::file_exists($dir);
}

$messages = array();
$cut = $_POST['operation'] == 'cut';
$l = OC_L10N::get('files_clipboard');
foreach($_POST['files'] as $file) {
	$source = \OC\Files\Filesystem::normalizePath(stripslashes($_POST['directory']) . '/' . $file);
	$target = \OC\Files\Filesystem::normalizePath(stripslashes($_POST['destination']) . '/' . $file);

	if (!\OC\Files\Filesystem::file_exists($source)) {
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

	if (\OC\Files\Filesystem::file_exists($target)) {
		if (!unlinkr($target)) {
			$messages[] = $l->t("Could not remove '%s'", array($file));
			continue;
		}
	}

	if ($cut) {
		if (!\OC\Files\Filesystem::rename($source, $target)) {
			$messages[] = $l->t("Could not move '%s'", array($file));
		}
	} else {
		if (!copyr($source, $target)) {
			$messages[] = $l->t("Could not copy '%s'", array($file));
		}
	}
	$error = error_get_last();
	if ($error) $messages[] = $error->message;
}
if (empty($messages)) OCP\JSON::success();
else OCP\JSON::error(array("messages" => $messages));

