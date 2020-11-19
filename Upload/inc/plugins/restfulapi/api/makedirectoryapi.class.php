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
class MakeDirectoryAPI extends RESTfulAPI {
	
	public function info() {
		return array(
			"name" => "Make directory",
			"description" => "This API allows users to make directories in a location specified in config/filedirectoryconfig.php.",
			"default" => "deactivated"
		);
	}
	/**
	This is where you perform the action when the API is called, the parameter given is an instance of stdClass, this method should return an instance of stdClass.
	*/
	public function action() {
		global $mybb, $db;
		include "inc/plugins/restfulapi/functions/filefunctions.php";
		include "inc/plugins/restfulapi/functions/jsonfunctions.php";
		include "inc/plugins/restfulapi/functions/stringfunctions.php";
		$configFileLocation = include "inc/plugins/restfulapi/config/filedirectoryconfig.php";
		$stdClass = new stdClass();
		$rawBody = file_get_contents("php://input");
		if (!($body = checkIfJson($rawBody))) {
			$stdClass->result = returnError("Invalid JSON data");
			return $stdClass;
		}
		$phpLocation = getKeyValue("location", $body);
		$phpContentType = $_SERVER["CONTENT_TYPE"];
		if (!checkIfTraversal(dirname($configFileLocation.$phpLocation), $configFileLocation)) {
			$error = ("Directory traversal check failed, or parent directory doesn't exist");
		}
		if (!checkIfSetAndString($phpLocation)) {
			$error = ("\"location\" key missing");
		}
		if ($phpContentType !== "application/json") {
			$error = ("\"content-type\" header missing, or not \"application/json\"");
		}
		if (file_exists($configFileLocation.$phpLocation)) {
			$error = ("Directory / file already exists");
		}
		if ($error) {
			$stdClass->result = returnError($error);
			return $stdClass;
		}
		if (mkdir($configFileLocation.$phpLocation)) {
			$stdClass->result = returnSuccess($phpLocation);
		} else {
			$stdClass->result = returnError("Directory creation failed");
		}
		return $stdClass;
	}

	public function activate() {
	}
	
	public function deactivate() {
	}
}
