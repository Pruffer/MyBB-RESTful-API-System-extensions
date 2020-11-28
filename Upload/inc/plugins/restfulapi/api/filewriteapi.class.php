<?php

# This file is a part of MyBB RESTful API System plugin - version 0.2
# Released under the MIT Licence by medbenji (TheGarfield)
# 
// Disallow direct access to this file for security reasons
if(!defined("IN_MYBB"))
{
	die("Direct initialization of this file is not allowed.<br /><br />Please make sure IN_MYBB is defined.");
}

/**
This interface should be implemented by APIs, see VersionAPI for a simple example.
*/
class FileWriteAPI extends RESTfulAPI {
	
	public function info() {
		return array(
			"name" => "File write",
			"description" => "This API allows users to write files.",
			"default" => "deactivated"
		);
	}
	/**
	This is where you perform the action when the API is called, the parameter given is an instance of stdClass, this method should return an instance of stdClass.
	*/
	public function action() {
		global $mybb, $db;
		include "inc/plugins/restfulapi/functions/filefunctions.php";
		include "inc/plugins/restfulapi/functions/varfunctions.php";
		include "inc/plugins/restfulapi/functions/stringfunctions.php";
		$configFileLocation = $mybb->settings["apifilelocation"];
		$stdClass = new stdClass();
		$phpData = array();
		$rawBody = file_get_contents("php://input");
		if (!($body = checkIfJson($rawBody))) {
			throw new BadRequestException("Invalid JSON data.");
		}
		try {
			foreach($body as $key=>$data) {
				$phpData[$key] = $data;
			}
		}
		catch (Exception $e) {
			throw new BadRequestException("Unable to read JSON data.");
		}
		$phpContentType = $_SERVER["CONTENT_TYPE"];
		if (!checkIfTraversal($configFileLocation.$phpData["location"], $configFileLocation)) {
			throw new BadRequestException("Directory traversal check failed, or location doesn't exist.");
		}
		if (!checkIfSetAndString($phpData["location"]) || !checkIfSetAndString($phpData["content"]) || !checkIfSetAndString($phpData["location"])) {
			throw new BadRequestException("\"location\" key missing.");
		}
		if ($phpContentType !== "application/json") {
			throw new BadRequestException("\"content-type\" header missing, or not \"application/json\".");
		}
		$realLocation = realpath($configFileLocation.$phpData["location"])."/";
		if (is_dir($realLocation)) {
			throw new BadRequestException("Specified file is a directory.");
		}
		if (file_exists($realLocation.$phpData["location"]) && $phpData["overwrite"] === "no") {
			$phpData["location"] = time().".".$phpData["location"];
			while (file_exists($realLocation.$phpData["location"])) {
				$phpData["location"] = substr(md5(microtime()),rand(0,26),5).time().".".$phpData["location"];
			}
		}
		if ($phpData["append"] === "yes") {
			$writeMode = "a";
		} else {
			$writeMode = "w";
		}
		if ($file = fopen($realLocation.$phpData["location"], $writeMode)) {
			fwrite($file, $phpData["content"]);
            fclose($file);
			$stdClass->content = $phpData["content"];
			$stdClass->result = returnSuccess($phpData["location"]);
		} else {
			throw new BadRequestException("File write failed.");
		}
		return $stdClass;
	}

	public function activate() {
	}
	
	public function deactivate() {
	}
}
