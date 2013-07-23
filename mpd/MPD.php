<?php namespace PHPMPDClient;
/**
 * Created by Mutant Labs
 * User: mijahn
 * Date: 31/05/13
 * Time: 11:44
 * 
 * Updated and revised by MonsterGfx
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
		static::$fp = fsockopen($host, $port, $errno, $errstr, 30); //Connect-String

		// check to see if we successfully connected
		if(!static::$fp) {
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
					throw new MPDLoginFailedException("Unable to log in. Incorrect password?");
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

		// wrap the remaining arguments in double quotes
		array_walk($args, function(&$value, $key){ $value = '"'.$value.'"'; });

		// build the command string
		$command = trim($method.' '.implode(' ',$args));

		// send the command to the server
		fputs(static::$fp, "$command\n");

		// keep looping while we're getting data
		while(!feof(static::$fp)) {
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
			"values" => $ret
		);

		// return the response
		return $sentResponse;
	}

	/**
	 * Add a resource to the playlist
	 *
	 * @param string $string 
	 * The item to add
	 *
	 * @return array
	 * The response from the server
	 */
	public static function add($string) {
		return static::send("add",$string);
	}

	/**
	 * Request the server status
	 *
	 * @return array
	 * The response from the server
	 */
	public static function status() {
		return static::send("status");
	}

	/**
	 * Clear the current playlist
	 * 
	 * @return array
	 * The response from the server
	 */
	public static function clear() {
		return static::send("clear");
	}

	/**
	 * Get the current song info
	 * 
	 * @return array
	 * The response from the server
	 */
	public static function currentSong() {
		return static::send("currentsong");
	}

	/**
	 * Move a song within the current playlist
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
	public static function move($from,$to) {
		return static::send("move", $from,$to);
	}
}


class MPDConnectionFailedException extends \Exception {};

class MPDLoginFailedException extends \Exception {};






