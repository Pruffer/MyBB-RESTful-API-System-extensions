<?php

# This file is a part of MyBB RESTful API System plugin - version 0.2
# Released under the MIT Licence by medbenji (TheGarfield)
# Modified by Prüffer (avantheim.org)

// Disallow direct access to this file for security reasons
if(!defined("IN_MYBB"))
{
	die("Direct initialization of this file is not allowed.<br /><br />Please make sure IN_MYBB is defined.");
}

/**
This interface should be implemented by APIs, see VersionAPI for a simple example.
*/
class ThreadAPI extends RESTfulAPI {
	
	public function info() {
		return array(
			"name" => "Thread",
			"description" => "This API exposes threads and posts.",
			"default" => "activated"
		);
	}
	/**
	This is where you perform the action when the API is called, the parameter given is an instance of stdClass, this method should return an instance of stdClass.
	*/
	public function action() {
		global $mybb, $db;
		require_once MYBB_ROOT . "inc/plugins/restfulapi/functions/jsonfunctions.php";
		require_once MYBB_ROOT . "inc/plugins/restfulapi/functions/varfunctions.php";
		$stdClass = new stdClass();
		$rawBody = file_get_contents("php://input");
		if (!($body = checkIfJson($rawBody))) {
			throw new BadRequestException("Invalid JSON data.");
		}
		$phpAction = getKeyValue("action", $body);
		$phpThreadId = getKeyValue("threadid", $body);
		$phpContentType = $_SERVER["CONTENT_TYPE"];
		if ($phpContentType !== "application/json") {
			$error = ("\"content-type\" header missing, or not \"application/json\".");
		}
		if(!checkIfSetAndString($phpAction)) {
			$error = ("\"action\" key missing.");
		}
		if(checkIfSetAndString($phpThreadId)) {
			$query = $db->simple_select('threads', 'tid', 'tid=\''.$phpThreadId.'\'');
			$queryResult = $db->fetch_array($query);
			if (!$queryResult) {
				$error = ("Thread ID doesn't exist.");
			}
		}
		if ($error) {
			throw new BadRequestException($error);
		}
		switch (strtolower($phpAction)) {
			case "list" :
				if(checkIfSetAndString($phpThreadId) && isset($forums[$phpThreadId])) {
					return (object) $forums[$phpThreadId];
				}
				else {
					return (object) $forums;
				}
			break;
			case "posts" :
				if(checkIfSetAndString($phpThreadId)) {
					$posts = array();
					$tid = $db->escape_string($phpThreadId);
					$query = $db->write_query("SELECT * FROM ".TABLE_PREFIX."posts p WHERE p.`tid` = '{$tid}'");
					while($post = $db->fetch_array($query)) {
						$posts[$post["pid"]] = $post;
					}
					return (object) $posts;
				}
				else {
					throw new BadRequestException("No thread ID specified.");
				}
			break;
			case "permissions" :
				$forumpermissions = forum_permissions();
				return (object) $forumpermissions;
			default:
			break;
		}
		throw new BadRequestException("No valid option given in the URL.");
	}
}