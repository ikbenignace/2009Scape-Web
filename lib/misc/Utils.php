<?php

/**
 * A utility class.
 * @author Adam Rodrigues
 *
 */
class Utils
{

    public static function time_elapsed_string($ptime)
    {
        $a = array(365 * 24 * 60 * 60 => 'year',
            30 * 24 * 60 * 60 => 'month',
            24 * 60 * 60 => 'day',
            60 * 60 => 'hour',
            60 => 'minute',
            1 => 'second'
        );
        $a_plural = array('year' => 'years',
            'month' => 'months',
            'day' => 'days',
            'hour' => 'hours',
            'minute' => 'minutes',
            'second' => 'seconds'
        );
        $etime = time() - $ptime;
        if ($etime < 1) {
            return '0 seconds';
        }
        foreach ($a as $secs => $str) {
            $d = $etime / $secs;
            if ($d >= 1) {
                $r = round($d);
                return $r . ' ' . ($r > 1 ? $a_plural[$str] : $str);
            }
        }
    }

    /**
     * Checks the passwords.
     * @param unknown $password1
     * @param unknown $passwordVerify
     */
    public static function checkPasswords($password1, $passwordVerify)
    {
        if (!ctype_alnum($password1)) {
            return "Passwords may only contain letters and numbers.";
        }
        if (strlen($password1) < 3 || strlen($password1) > 20) {
            return "Passwords must be between 3 and 20 characters long.";
        }
        if (strcasecmp($password1, $passwordVerify) !== 0) {
            return "Your new passwords must match!";
        }
        return "";
    }

    /**
     * Creates a random salt for a password.
     * @param number The number length.
     */
    public static function random_salt($name_length = 22)
    {
        $alpha_numeric = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
        return "$2a$12$" . substr(str_shuffle($alpha_numeric), 0, $name_length);
    }

    public static function getFormatUsername($username)
    {
        return ucwords(str_replace("_", " ", $username));
    }

    public static function formatTime($time)
    {
        $timestamp = strtotime($time);
        return date("F d, Y - g:i A", $timestamp);
    }


    public static function purify($html)
    {
        $config = HTMLPurifier_Config::createDefault();
        $config->set('HTML.AllowedElements', '');
        $config->set('Attr.AllowedClasses', '');
        $config->set('HTML.AllowedAttributes', '');
        $config->set('AutoFormat.RemoveEmpty', true);
        $purifier = new HTMLPurifier($config);
        return $purifier->purify($html);
    }

    public static function startsWith($haystack, $needle)
    {
        $length = strlen($needle);
        return (substr($haystack, 0, $length) === $needle);
    }

    public static function endsWith($haystack, $needle)
    {
        $length = strlen($needle);
        if ($length == 0) {
            return true;
        }

        return (substr($haystack, -$length) === $needle);
    }

}

?>