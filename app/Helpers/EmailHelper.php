<?php

namespace App\Helpers;
use Mail;

class EmailHelper
{
    public static function sendEmailFromRegPartner($data)
    {
        $to_emails = ['partner@mytripline.com']; // partner@mytripline.com
        $subject = 'Регистрация нового пользователя - '. $data['name'];

        Mail::send('emails.request', [ 'data_send' => $data ], function($message) use ($to_emails, $subject) {
            $message->to($to_emails)->subject($subject);
            $message->from('test123@mytripline.com');
        });
    }

    public static function sendEmailFromPasswordReset($data)
    {
        $to_emails = ['partner@mytripline.com']; // partner@mytripline.com
        $subject = 'Регистрация нового пользователя - '. $data['name'];

        Mail::send('emails.request', [ 'data_send' => $data ], function($message) use ($to_emails, $subject) {
            $message->to($to_emails)->subject($subject);
            $message->from('test123@mytripline.com');
        });
    }

    public static function sendEmailFromForgotPassword($data, $toEmail)
    {
        $subject = 'You forgot password code';

        Mail::send('emails.forgot-pass', [ 'code' => $data ], function($message) use ($toEmail, $subject) {
            $message->to($toEmail)->subject($subject);
            $message->from('test123@mytripline.com');
        });
    }

    public static function sendEmailFromUpdateRoute($data, $toEmail)
    {
        $subject = 'Ride offer';

        Mail::send('emails.ride-offer', [ 'data' => $data ], function($message) use ($toEmail, $subject) {
            $message->to($toEmail)->subject($subject);
            $message->from('notification@mytripline.com');
        });
    }
}
