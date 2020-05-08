<?php

/**
 * A class representation of a template.
 * @author Adam Rodrigues
 *
 */
class Template
{

    /**
     * The editing template.
     */
    private $template;

    /**
     * The original template.
     */
    private $originalTemplate;

    /**
     * Constructs a template with content.
     * @param templateContents the template content.
     */
    public function __construct($templateContents = "")
    {
        $this->template = $this->originalTemplate = $templateContents;
    }

    /**
     * Inserts content into a specific indentifier
     * @param unknown $identifier - place in template to insert
     * @param unknown $content - content to insert
     */
    public function insert($identifier, $content)
    {
        $this->template = str_replace("[$" . $identifier . "]", $content, $this->template);
    }

    /**
     * Remove text between two places.
     * @param beggining The beggining.
     * @param end The end.
     */
    function removeBetween($beginning, $end)
    {
        $this->replaceBetween($beginning, $end, "");
    }

    /**
     * Replace text between two places.
     * @param beggining The beggining.
     * @param end The end.
     * @param replace The replaced text.
     */
    function replaceBetween($beginning, $end, $replace)
    {
        $beginningPos = strpos($this->template, $beginning);
        $endPos = strpos($this->template, $end);
        if ($beginningPos === false || $endPos === false) {
            return false;
        }
        $textToDelete = substr($this->template, $beginningPos, ($endPos + strlen($end)) - $beginningPos);
        $this->template = str_replace($textToDelete, $replace, $this->template);
        return true;
    }

    /**
     * Removes all text between two places.
     * @param beggining The beggining.
     * @param end The end.
     */
    function removeAll($beginning, $end)
    {
        while ($this->replaceBetween($beginning, $end, "")) {
        }
    }

    /**
     * Appends content to the end of the template
     * @param string $content - content to be appended
     */
    public function append($content)
    {
        $this->template .= $content;
    }

    /**
     * Displays the template
     * @param boolean $identifiers - false to remove unused identifiers
     */
    public function display($identifiers = false)
    {
        if (!$identifiers) {
            $this->removeAll("[$", "]");
        }
        echo $this->template;
    }

    /**
     * Resets the template to the original form
     */
    public function reset()
    {
        $this->template = $this->originalTemplate;
    }

    /**
     * Returns the template html
     * @return
     */
    public function getContents()
    {
        return $this->template;
    }

}

?>