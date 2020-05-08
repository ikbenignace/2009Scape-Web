<?php
define("GLOBAL_DB", "imellaeu_global");
define("SERVER_DB", "imellaeu_rs");
define("FORUM_DB", "imellaeu_web");

/**
 * Represents a database connection.
 * @author Adam Rodrigues
 *
 */
class Database
{

    /**
     * The ip of the database.
     */
    private $IP;

    /**
     * The username.
     */
    private $username;

    /**
     * The password.
     */
    private $password;

    /**
     * The database name.
     */
    private $database;

    /**
     * The object connection.
     */
    private $connection;

    /**
     * The query.
     */
    private $query = 0;

    /**
     * Constructs a database.
     * @param ip The ip.
     * @param username The username.
     * @param password The password.
     * @param database The database.
     */
    public function __construct($IP, $username, $password, $database)
    {
        $this->IP = $IP;
        $this->username = $username;
        $this->password = $password;
        $this->database = $database;
    }

    /**
     * Connects to the database.
     */
    public function connect()
    {
        $this->connection = new PDO('mysql:host=' . $this->IP . ';dbname=' . $this->database . ';charset=utf8', $this->username, $this->password);
        $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->connection->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
    }

    /**
     * Switches the database connection.
     * @param db The database name.
     */
    public function switchDatabase($db)
    {
        mysqli_select_db($this->connection, $db);
    }

    /**
     * Prepare an sql query.
     * @param sql The prepared sql.
     */
    public function prepare($sql)
    {
        return $this->connection->prepare($sql);
    }

    /**
     * Sends an sql query.
     * @param query The query.
     */
    public function query($sql)
    {
        return $this->connection->query($sql);
    }

    /**
     * Checks if the query is successfull.
     */
    public function success()
    {
        return mysqli_affected_rows($this->connection) > 0;
    }

    /**
     * Fetches an array.
     */
    public function fetchArray($data)
    {
        return $data->fetch();
    }

    /**
     * Gets the database connection.
     */
    public function getConnection()
    {
        return $this->connection;
    }

    /**
     * Gets the mysql error.
     */
    public function getError()
    {
        return mysqli_error($this->connection);
    }

    /**
     * Escapes a mysql string.
     */
    public function escape($string)
    {
        return $string;
    }

    /**
     * Number of rows in the data.
     */
    public function num_rows($row)
    {
        return mysqli_num_rows($row);
    }

}

?>