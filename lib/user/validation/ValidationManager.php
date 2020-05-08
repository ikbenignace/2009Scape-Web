<?php

/**
 * A managing class for validating requests.
 * @author Clayton Williams
 *
 */
class ValidationManager
{

    /**
     * The types of validating.
     */
    private static $TYPES = array(1 => "CONFIRM_EMAIL", 2 => "REQ_UPDATE_EMAIL", 3 => "UPDATE_EMAIL", 4 => "RECOVER_PASSWORD");

    /**
     * The database connection.
     */
    private $db;

    /**
     * The validations from the database.
     */
    private $validations = array();

    /**
     * Constructs the validation manager.
     */
    public function __construct()
    {
        $this->db = Registry::get("database");
        $query = $this->db->query("SELECT * FROM validations");
        while ($row = $query->fetch()) {
            $validation = new Validation($row['username'], $row['code'], $row['type'], $row['timestamp'], $row['value']);
            array_push($this->validations, $validation);
        }
    }

    /**
     * Handle the validation of a code.
     */
    public function handleValidation($code)
    {
        $validation = $this->getValidation($code);
        if ($validation) {
            switch ($validation->type) {
                case 1:
                    $this->checkLogin();
                    return Registry::get("user")->getModule("EmailPreference")->handleValidation($code, $validation);
                case 2:
                    $this->checkLogin();
                    header("Location: /account/index.php?req=update-email&code=" . $code);
                    exit();
                    return "";
                case 4:
                    return Registry::get("user")->getModule("RecoverPassword")->handleValidation($code, $validation);
            }
        }
        return "This validation code is either invalid or has expired.<br>";
    }

    /**
     * Deletes a validation code.
     * @param code The code.
     */
    public function deleteCode($code)
    {
        $this->db->query("DELETE FROM validations WHERE code='" . $code . "'");
    }

    /**
     * Inserts a new validation.
     * @param username The username.
     * @param type The type.
     * @param value The random value if set.
     */
    public function newValidation($username, $type, $value = "")
    {
        $random = $this->random_string();
        $statement = $this->db->query("DELETE FROM validations WHERE username='" . $username . "' AND type='" . $type . "'");
        $statement->execute();
        $this->db->query("INSERT INTO validations (username, code, type, value) VALUES('" . $username . "', '" . $random . "', '" . $type . "', '" . $value . "');");
        return $random;
    }

    /**
     * Creates a random string.
     * @param the length.
     */
    private function random_string($name_length = 30)
    {
        $alpha_numeric = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789!';
        return substr(str_shuffle($alpha_numeric), 0, $name_length);
    }

    /**
     * Gets the validation from the code.
     * @param code The code.
     */
    public function getValidation($code)
    {
        foreach ($this->validations as $v) {
            if ($v->code == $code) {
                return $v;
            }
        }
        return false;
    }

    /**
     * Checks if the user is logged in.
     */
    public function checkLogin()
    {
        $user = Registry::get("user");
        if (!$user->isLoggedIn()) {
            echo "Error - Not logged in.";
            exit;
        }
    }
}

/**
 * A validation description.
 * @author Clayton Williams
 *
 */
class Validation
{

    /**
     * The username.
     */
    public $username;

    /**
     * The validation code.
     */
    public $code;

    /**
     * The type of validation.
     */
    public $type;

    /**
     * The time stamp.
     */
    public $timestamp;

    /**
     * The random validation value.
     */
    public $value;

    /**
     * Constructs a validation.
     * @param unknown $username
     * @param unknown $code
     * @param unknown $type
     * @param unknown $timestamp
     */
    public function __construct($username, $code, $type, $timestamp, $value)
    {
        $this->username = $username;
        $this->code = $code;
        $this->type = $type;
        $this->timestamp = $timestamp;
        $this->value = $value;
    }

    /**
     * Gets the username of a validation.
     */
    public function getUsername()
    {
        return $this->username;
    }

    public function getValue()
    {
        return $this->value;
    }
}

?>