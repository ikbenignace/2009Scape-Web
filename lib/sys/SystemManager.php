<?php
if (!defined("_VALID_PHP")) {
    die ('Direct access to this location is not allowed.');
}
//Global Initializations
require_once($_SERVER['DOCUMENT_ROOT'] . "/lib/misc/htmlpurifier/HTMLPurifier.standalone.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/lib/template/TemplateManager.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/lib/misc/phpmailer/PHPMailerAutoload.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/lib/sys/SecurityManager.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/lib/user/validation/ValidationManager.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/lib/module/ModuleManager.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/lib/forums/ForumManager.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/lib/misc/Utils.php");


/**
 * Acts as a managing class for all system required functions.
 * @author Adam Rodrigues
 *
 */
class SystemManager
{

    /**
     * The security manager.
     */
    private $securityManager;

    /**
     * The validation manager instance.
     */
    private $validationManager;

    /**
     * The forum manager.
     */
    private $forumManager;

    /**
     * The database instance.
     */
    private $db;

    /**
     * Configures the system manager.
     */
    public function configure()
    {
        $this->db = Registry::get("database");
        $this->securityManager = new SecurityManager();
        $this->validationManager = new ValidationManager();
        if (defined('FORUMS')) {
            $this->configureForums();
        }
    }

    /**
     * Configures the forum manager.
     */
    public function configureForums()
    {
        $this->forumManager = new ForumManager();
        $this->forumManager->configure();
    }

    /**
     * Displays a PHP structure.
     * @param $name The name of the structure.
     */
    public function displayStruct($name)
    {
        require_once($_SERVER['DOCUMENT_ROOT'] . "/lib/structs/" . $name . ".php");
    }

    /**
     * Sends an email to a user.
     * @param user The user.
     * @param body The body of the email.
     */
    public function sendEmail($user, $body, $email = "")
    {
        if ($email == "") {
            $email = $user->getEmail();
        }
        try {
            $mail = new PHPMailer;
            $mail->isSMTP();
            $mail->Host = MAIL_HOST;
            $mail->SMTPAuth = true;
            $mail->Username = EMAIL;
            $mail->Password = EMAIL_PASS;
            $mail->SMTPSecure = 'tls';
            $mail->Port = 587;
            $mail->From = EMAIL;
            $mail->FromName = SITE_NAME . ' Account Support';
            $mail->addAddress($email, $user->getUsername());
            $mail->isHTML(true);
            $mail->Subject = SITE_NAME . ' Account Support';
            $mail->Body = $body;
            $mail->send();
        } catch (phpmailerException $e) {
            echo "Error m - " . $e->errorMessage();
        } catch (Exception $e) {
            echo "Error exception - " . $e->getMessage();
        }
    }

    /**
     * Logs a message to be saved into the database.
     * @param message The message.
     * @param type The type of log.
     */
    public function log($message, $type = 0)
    {
        $statement = $this->db->prepare("INSERT INTO sys_logs (message, log_type, IP_ADDRESS) VALUES(?, ?, ?);");
        $statement->execute(array($message, $type, $_SERVER['REMOTE_ADDR']));
    }

    /**
     * Returns a number of total players accross the span of worlds.
     * @return The number of players.
     */
    public function getPlayersOnline()
    {
        $count = 0;
        $worlds = $this->db->query("SELECT * FROM worlds");
        while ($world = $worlds->fetch(PDO::FETCH_ASSOC)) {
            $count += $world['players'] < 0 ? 0 : $world['players'];
        }
        return $count;
    }

    /**
     * Gets the validation manager.
     */
    public function getValidationManager()
    {
        return $this->validationManager;
    }

    /**
     * Gets the security manager.
     */
    public function getSecurityManager()
    {
        return $this->securityManager;
    }

    /**
     * Gets the forum manager.
     */
    public function getForumManager()
    {
        return $this->forumManager;
    }

}

?>