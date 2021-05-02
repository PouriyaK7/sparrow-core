<?php

namespace Sparrow;

class Telegram
{

    const TEL_URL = 'https://api.telegram.org/bot';
    private static string $reqUrl = '';

    private static function sendReq($method, $data)
    {
        if (!self::$reqUrl)
            self::$reqUrl = self::TEL_URL . Setting::get('telegram_bot_token') . '/';
        return json_decode(file_get_contents(self::$reqUrl . $method . '?' . http_build_query($data)));
    }

    public static function sendMessage($chatId, $text, $reply = null)
    {
        $data = ['chat_id' => $chatId, 'text' => $text, 'allow_sending_without_reply' => true, 'parse_mode' => 'HTML'];
        if ($reply ?? 0)
            $data['reply_to_message_id'] = $reply;
        return self::sendReq('sendMessage', $data);
    }

    public static function setWebhook($url)
    {
        return self::sendReq('setWebhook', ['url' => $url]);
    }

    public static function addUser($chat_id, $first_name, $last_name, $username, $socialType, $userID)
    {
        $stmt = DB::connect()->prepare('INSERT INTO `social_logins` (`social_id`, `first_name`, `last_name`, `username`, `social`, `user_id`) VALUES (?, ?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE `first_name` = ?, `last_name` = ?, `username` = ?');
        $stmt->bind_param('ssssiisss', $chat_id, $first_name, $last_name, $username, $socialType, $userID, $first_name, $last_name, $username);
        $stmt->execute();
    }

    public static function getMember($chat_id, $user_id)
    {
        return self::sendReq('getChatMember', ['chat_id' => $chat_id, 'user_id' => $user_id]);
    }

    public static function getUser($socialID)
    {
        return DB::prepare(
            'SELECT * FROM `social_logins` WHERE `social_id` = ?',
            null,
            DB::FETCH_ALL,
            's',
            $socialID
        )[0] ?? [];
    }
}