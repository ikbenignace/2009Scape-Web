<?php

/**
 * Handles a vote request.
 * @author Adam Rodrigues
 *
 */
class VoteRequest extends Action
{

    /**
     * Handles a request.
     * @param cleaned The parameters.
     */
    public function handle($cleaned)
    {
        Registry::get("sys")->log("Incoming vote request -> http://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'], VOTE_LOG);
        if (strpos($_SERVER['REQUEST_URI'], '&?') && strpos($_SERVER['REQUEST_URI'], 'usr')) {
            $data = $_SERVER['REQUEST_URI'];
            $whatIWant = substr($data, strpos($data, "usr=") + 1);
            $whatIWant = str_replace("sr=", "", $whatIWant);
            $_GET['usr'] = $whatIWant;
        }
        $database = Registry::get("database");
        $get = "";
        $statement = null;
        foreach ($_GET as $key => $value) {
            $statement = $database->query("SELECT * FROM voting_sites WHERE get_command='" . $key . "'");
            if ($statement->rowCount() > 0) {
                $get = $key;
                break;
            }
        }
        if ($statement == null || !$statement) {
            echo "Statement error.";
            Registry::get("sys")->log("Voting Request statement error.");
            return;
        }
        if ($site = $statement->fetch(PDO::FETCH_ASSOC)) {
            $ip_request = $_SERVER["HTTP_CF_CONNECTING_IP"];
            if (isset($site['host_name']) && $site['host_name'] != "") {
                $hostip = gethostbyname($site['host_name']);
                if ($ip_request != $hostip) {
                    echo "Invalid request! Ip request = " . $ip_request . " host ip that is validated = " . $hostip . "\n";
                    Registry::get("sys")->log("Vote IP mismatch! Ip request = " . $ip_request . " host ip that is validated = " . $hostip . "", VOTE_LOG);

                }
            }
            $username = $this->parseUsername($_GET[$get]);
            $user = new User($username);
            $user->create($username);
            if (!$user->exists()) {
                echo "Error! Username does not exist.";
                return;
            }
            if (!$user->getModule("VotingModule")->canVote($site, $username)) {
                echo "Error! User has to wait to vote again.";
                return;
            }
            $user->addCredits($site['credits'] * ($user->getDonatorType() == 1 ? 2 : 1));
            $query = $database->query("SELECT * FROM votes WHERE username='" . $user->getUsername() . "' AND site='" . $site['name'] . "'");
            if ($query->rowCount() == 0) {
                $database->query("INSERT INTO votes (username, site, timestamp) VALUES('" . $user->getUsername() . "', '" . $site['name'] . "', NOW())");
            } else {
                $database->query("UPDATE votes SET timestamp=NOW() where username='" . $user->getUsername() . "' AND site='" . $site['name'] . "'");
            }
            $user->write();
            Registry::get("sys")->log("The user -> " . ucwords($user->getUsername()) . " voted on " . $site['name'], VOTE_LOG);
            return;
        }
        Registry::get("sys")->log("Invalid vote request sent from http://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'], VOTE_LOG);
    }

    /**
     * Parses a username.
     * @param username The username.
     * @return The parsed username.
     */
    private function parseUsername($username)
    {
        $username = stripslashes($username);
        $username = strtolower($username);
        return $username;
    }

}

?>