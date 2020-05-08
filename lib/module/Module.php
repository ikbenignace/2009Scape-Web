<?php

/**
 * A module for a template.
 * @author Adam Rodrigues
 *
 */
class Module
{

    /**
     * The associated template.
     */
    protected $template;

    /**
     * The database connection.
     */
    protected $database;

    /**
     * The user using the module.
     */
    protected $user;

    /**
     * Constructs the module.
     * @param user The user.
     */
    public function __construct($user)
    {
        $this->database = Registry::get("database");
        $this->user = $user;
        $this->template = new Template;
    }

    /**
     * Append output to the template
     * @param out The text to append.
     */
    protected function appendOutput($out)
    {
        $this->template->append($out);
    }

    /**
     * Appends an output to the template.
     * @param output The output.
     */
    protected function append($output)
    {
        $this->appendOutput($output);
    }

    /**
     * Loading method.
     */
    public function load()
    {
    }

    /**
     * Handles post requests.
     */
    public function handlePost($post)
    {
    }

    /**
     * Handles a module action.
     * @param action The action command.
     * @param cleaned The parameters.
     */
    public function handleAction($action, $cleaned)
    {
    }

    /**
     * Displays the module template
     * Resets template to default afterwards
     */
    public function display($resetTemplate = true)
    {
        $this->template->display();
        if ($resetTemplate)
            $this->template->reset();
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
            return false;
        }
        return true;
    }

    /**
     * Gets the templates content.
     */
    protected function getTemplateContents()
    {
        return $this->template->getContents();
    }

    /**
     * Gets the associated template.
     */
    public function getTemplate()
    {
        return $this->template;
    }

    /**
     * Gets the database.
     */
    public function getDatabase()
    {
        return $this->database;
    }

    /**
     * Gets the user.
     */
    public function getUser()
    {
        return $this->user;
    }

}

?>