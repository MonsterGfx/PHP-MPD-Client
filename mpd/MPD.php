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
	private $fp;

	/**
	 * The response
	 */
	private $response;

	/**
	 * Construct the MPD wrapper object
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
	public function __construct($pass = "", $host = "", $port = 6600, $refresh = 0)
	{
		// open the connection to the server
		$this->fp = fsockopen($host,$port,$errno,$errstr,30); //Connect-String

		// check to see if we successfully connected
		if(!$this->fp) {
			// no connection
			throw new Exception("$errstr ($errno)");
		}

		// we did successfully connect

		// keep reading from the connection while we're getting data
		while(!feof($this->fp))
		{
			// get a line from the stream
			$got = fgets($this->fp,1024);

			// is the "MPD Ready" message? If so, leave the loop
			if(strncmp("OK",$got,strlen("OK"))==0)
				break;

			// set the response value
			$this->response = "$got<br>";

			// is it an "ACK" (error) message? if so, leave the loop
			if(strncmp("ACK",$got,strlen("ACK"))==0)
				break;
		}

		// do we have a password to send?
		if($pass != "")
		{
			// send the password
			fputs($this->fp,"password \"$pass\"\n"); //Check Password

			// keep reading while we're getting data from the stream
			while(!feof($this->fp))
			{
				// get a line from the stream
				$got = fgets($this->fp,1024);

				// is it the "Login OK" message? if so, leave the loop
				if(strncmp("OK",$got,strlen("OK"))==0)
					break;

				// save the response string
				$this->response = "$got<br>";

				// is it an "ACK" (error) message? if so, the login failed
				if(strncmp("ACK",$got,strlen("ACK"))==0)
					throw new Exception("Unable to log in. Incorrect password?");
			}
		}
	}

	/**
	 * Send a command to the server
	 *
	 * Our send method handles all commands and responses, you can use this
	 * directly or the quick method wrappers below.
	 * 
	 * @todo rewrite this to use func_get_args (or whatever that function is called)
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
	public function send($method, $arg1="", $arg2="")
	{
		// format the comm
		if($arg1 != "" && $arg2 != "")
		{
			$command = "$method \"$arg1\" \"$arg2\"";
		}
		elseif($arg1 != "") {
			$command = "$method \"$arg1\"";
		}
		else {
			$command = $method;
		}

		// send the command to the server
		fputs($this->fp,"$command\n");

		// keep looping while we're getting data
		while(!feof($this->fp)) {
			// get a line of data
			$got =  fgets($this->fp,1024);

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
			"response" => $this->response,
			// @todo why is this here? it's just the last line of the response
			"got" => $got,
			"ret" => $ret
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
	public function add($string) {
		return $this->send("add",$string);
	}

	/**
	 * Request the server status
	 *
	 * @return array
	 * The response from the server
	 */
	public function status() {
		return $this->send("status");
	}

	/**
	 * Clear the current playlist
	 * 
	 * @return array
	 * The response from the server
	 */
	public function clear() {
		return $this->send("clear");
	}

	/**
	 * Get the current song info
	 * 
	 * @return array
	 * The response from the server
	 */
	public function currentSong() {
		return $this->send("currentsong");
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
	public function move($from,$to) {
		return $this->send("move", $from,$to);
	}
}