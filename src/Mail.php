<?php


namespace Sparrow;


class Mail
{
    /**
     * @param $to
     * @param $subject
     * @param $message
     * @param string $from
     * @return bool
     */
    public static function send($to, $subject, $message, $from = ''): bool
    {
        $headers = '';
        if ($from)
            $headers .= "From: =?UTF-8?B?".base64_encode(Setting::get('site_title'))."?= <$from>\r\n";
        $headers .= "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
        $headers .= "X-Mailer: PHP/" . phpversion()."\r\n";
        return mail($to, "=?UTF-8?B?".base64_encode($subject)."?=", $message, $headers);
    }
}