<?php namespace PHPMPDClient;
/**
 * Created by Mutant Labs
 * User: mijahn
 * Date: 31/05/13
 * Time: 11:44
 *
 * Updated and revised by MonsterGfx
 */

/**
 * The MPD server interface
 */
class MPD {

	/**
	 * The file pointer used for accessing the server
	 */
	private static $fp = null;

	/**
	 * The response
	 */
	private static $response;

	/**
	 * Connect to the MPD server
	 *
	 * @param string $pass
	 * The password for the MPD server
	 *
	 * @param string $host
	 * The host name or IP address of the server
	 *
	 * @param int $port
	 * The port number via which to connect to the server
	 *
	 * @param int $refresh
	 * It is not known what this is supposed to be. It's not referenced in the
	 * code below.
	 */
	public static function connect($pass = "", $host = "", $port = 6600, $refresh = 0)
	{
		// open the connection to the server
		static::$fp = fsockopen($host, $port, $errno, $errstr, 30);

		// check to see if we successfully connected
		if(!static::$fp)
		{
			// no connection
			throw new MPDConnectionFailedException("$errstr ($errno)");
		}

		// we did successfully connect

		// keep reading from the connection while we're getting data
		while(!feof(static::$fp))
		{
			// get a line from the stream
			$got = fgets(static::$fp,1024);

			// is the "MPD Ready" message? If so, leave the loop
			if(strncmp("OK",$got,strlen("OK"))==0)
				break;

			// set the response value
			static::$response = "$got<br>";

			// is it an "ACK" (error) message? if so, leave the loop
			if(strncmp("ACK",$got,strlen("ACK"))==0)
				break;
		}

		// do we have a password to send?
		if($pass != "")
		{
			// send the password
			fputs(static::$fp,"password \"$pass\"\n"); //Check Password

			// keep reading while we're getting data from the stream
			while(!feof(static::$fp))
			{
				// get a line from the stream
				$got = fgets(static::$fp,1024);

				// is it the "Login OK" message? if so, leave the loop
				if(strncmp("OK",$got,strlen("OK"))==0)
					break;

				// save the response string
				static::$response = "$got<br>";

				// is it an "ACK" (error) message? if so, the login failed
				if(strncmp("ACK",$got,strlen("ACK"))==0)
					throw new MPDLoginFailedException();
			}
		}
	}

	/**
	 * Disconnect from the server
	 */
	public static function disconnect()
	{
		if(static::$fp!==null)
			fclose(static::$fp);
	}

	/**
	 * "Condense" an array to a single non-associative array of scalar values.
	 *
	 * @param array $arr
	 * @return array
	 */
	private static function condense($arr)
	{
		$result = array();
		foreach(array_values($arr) as $a)
		{
			if(is_scalar($a))
				$result[] = $a;
			elseif(is_array($a))
				$result = array_merge($result, static::condense($a));
			elseif(is_object($a))
				$result = array_merge($result, static::condense((array)$a));
			else
				throw new Exception("Unrecognized object type");
		}
		return $result;
	}

	/**
	 * Send a command to the server
	 *
	 * Our send method handles all commands and responses, you can use this
	 * directly or the quick method wrappers below.
	 *
	 * @param string $method
	 * The method (command) string
	 *
	 * @param string $arg1
	 * The first argument
	 *
	 * @param string $arg2
	 * The second argument
	 *
	 * @return string
	 * The response
	 */
	public static function send()
	{
		// get the arguments
		$args = func_get_args();

		// the first argument is the method
		$method = array_shift($args);

		// now I want to handle any arrays in the arguments & build a single,
		// non-associative array of arguments whose elements are all scalar
		// values
		$args = static::condense($args);

		// wrap the remaining arguments in double quotes (escaping any quotes in
		// the process)
		array_walk($args, function(&$value, $key){
			$value = str_replace('"', '\"', $value);
			$value = str_replace("'", "\\'", $value);
			$value = '"'.$value.'"';
		});

		// build the command string
		$command = trim($method.' '.implode(' ',$args));

		// send the command to the server
		fputs(static::$fp, "$command\n");

		$ret = array();

		// keep looping while we're getting data
		while(!feof(static::$fp))
		{
			// get a line of data
			$got =  fgets(static::$fp, 1024);

			// is the "OK" message? if so, leave the loop
			if(strncmp("OK", $got, strlen("OK"))==0)
				break;

			// is the "ACK" (error) message? if so, leave the loop
			if(strncmp("ACK", $got, strlen("ACK"))==0)
				break;

			// add whatever we got from the server to our list of strings
			$ret[]=$got;
		}

		// build a response array
		$sentResponse = array(
			"response" => static::$response,
			"status" => trim($got),
			"values" => $ret,
		);

		// return the response
		return $sentResponse;
	}

	/**
	 * Add a resource to the playlist
	 *
	 * add {URI} - Adds the file URI to the playlist (directories add
	 * recursively). URI can also be a single file.
	 *
	 * @param string $string
	 * The item to add
	 *
	 * @return array
	 * The response from the server
	 */
	public static function add($string)
	{
		// validation
		// the argument can be just about anything (I can't find a description
		// of the URI), so I'm just going to make sure it's a non-empty scalar
		// value
		if(!$string || !is_scalar($string))
			throw new MPDInvalidArgumentException("Add: invalid argument: $string");

		// send the command
		return static::send("add", $string);
	}

	/**
	 * Request the server status
	 *
	 * Reports the current status of the player and the volume level.
	 *
	 * @return array
	 * The response from the server
	 */
	public static function status()
	{
		return static::send("status");
	}

	/**
	 * Clear the current playlist
	 *
	 * @return array
	 * The response from the server
	 */
	public static function clear()
	{
		return static::send("clear");
	}

	/**
	 * Get the current song info
	 *
	 * Displays the song info of the current song (same song that is identified
	 * in status).
	 *
	 * @return array
	 * The response from the server
	 */
	public static function currentSong()
	{
		return static::send("currentsong");
	}

	/**
	 * Move a song within the current playlist
	 *
	 * move [{FROM} | {START:END}] {TO} - Moves the song at FROM or range of
	 * songs at START:END to TO in the playlist.
	 *
	 * @param string $from
	 * The Song ID of the song to move
	 *
	 * @param string $to
	 * The playlist position to move to
	 *
	 * @return array
	 * The response from the server
	 */
	public static function move($from, $to)
	{
		// validation

		// $from must be either a single integer value (a playlist position) or
		// a string describing a range (two integers separated by ':')
		if(!preg_match('/^([0-9]+|[0-9]+\:[0-9]+)$/', trim($from)))
			throw new MPDInvalidArgumentException("Move: invalid FROM value: $from");

		// $to must be a single integer value
		if(!is_numeric($to) || $to<0)
			throw new MPDInvalidArgumentException("Move: invalid TO value: $to");

		// send the command
		return static::send("move", $from, $to);
	}
}


class MPDConnectionFailedException extends \Exception {};

class MPDLoginFailedException extends \Exception {};

class MPDInvalidArgumentException extends \Exception {};






