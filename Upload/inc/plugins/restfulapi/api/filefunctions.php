<?php
function checkIfTraversal($path, $location) {
	$realPath = realpath($path);
	$realLocation = realpath($location);
	if ($realPath === false || strpos($realPath, $realLocation) !== 0) {
		return false;
	} else {
		return true;
	}
}
function checkIfFilenameDirectory($filenamePath, $locationPath) {
	if (realpath($filenamePath) === false || realpath($filenamePath) !== realpath($locationPath)) {
		return false;
	} else {
		return true;
	}
}
if (file_exists($location.$filename) && $overwrite === "no") {
	$filename = time().".".$filename;
	while (file_exists($location.$filename)) {
		$filename = substr(md5(microtime()),rand(0,26),5).time().".".$filename;
	}
	return $filename;
}
?>