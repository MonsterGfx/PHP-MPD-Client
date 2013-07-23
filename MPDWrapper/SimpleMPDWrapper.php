<?php
/**
 * Created by Mutant Labs
 * User: mijahn
 * Date: 31/05/13
 * Time: 11:44
 */

namespace MPDWrapper;

class SimpleMPDWrapper {
    private $fp;
    private $response;
    public function __construct($pass = "",$host = "",$port = 6600,$refresh = 0) {

        if(!isset($srch)) {

        }
        $this->fp = fsockopen($host,$port,$errno,$errstr,30); //Connect-String
        if(!$this->fp) {
            echo "$errstr ($errno)<br>\n"; //Can we connect?
        }
        else {
            while(!feof($this->fp)) {
                $got = fgets($this->fp,1024);
                if(strncmp("OK",$got,strlen("OK"))==0) //MPD Ready...
                break;
                $this->response = "$got<br>";
                if(strncmp("ACK",$got,strlen("ACK"))==0) //What"s going wrong?
                break;
            }
            if($pass != "") { //Password needed?
                fputs($this->fp,"password \"$pass\"\n"); //Check Password
                while(!feof($this->fp)) {
                    $got = fgets($this->fp,1024);
                    if(strncmp("OK",$got,strlen("OK"))==0) { //Password OK
                        #print "Login Succesful<br>\n";
                        break;
                    }
                    $this->response = "$got<br>";
                    if(strncmp("ACK",$got,strlen("ACK"))==0) //Password Wrong
                    break;
                    die("Wrong Password?");
                }
            }
        }

    }


    public function send($method,$arg1="",$arg2="") {

        // if we have a string, send it as well
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


        fputs($this->fp,"$command\n"); //Do desired action!
        $c = 0;
        while(!feof($this->fp)) {
            $got =  fgets($this->fp,1024);
            if(strncmp("OK",$got,strlen("OK"))==0)
                break;
            if(strncmp("ACK",$got,strlen("ACK"))==0)
                break;

            $ret[$c]=$got;
            $c++;
        }

        $sentResponse = array(
            "response" => $this->response,
            "got" => $got,
            "ret" => $ret
        );
        return $sentResponse;

    }


    public function add($string) {
        return $this->send("add",$string);
    }

    public function status() {
        return $this->send("status"); // no second parameter null as status requires no args
    }

    public function clear() {
        return $this->send("clear");
    }

    public function currentSong() {
        return $this->send("currentsong");
    }

    public function move($from,$to) {
        return $this->send("move", $from,$to);
    }
	/**
	 * The file pointer used for accessing the server
	 */
	/**
	 * The response
	 */
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
	/**
	 * Add a resource to the playlist
	 *
	 * @param string $string 
	 * The item to add
	 *
	 * @return array
	 * The response from the server
	 */
	/**
	 * Request the server status
	 *
	 * @return array
	 * The response from the server
	 */
	/**
	 * Clear the current playlist
	 * 
	 * @return array
	 * The response from the server
	 */
	/**
	 * Get the current song info
	 * 
	 * @return array
	 * The response from the server
	 */
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
}