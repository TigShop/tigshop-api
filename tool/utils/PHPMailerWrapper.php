<?php

namespace utils;


use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;

/**
 * 发送邮件
 */
class PHPMailerWrapper
{
    protected $mailer;

    public function __construct()
    {
        $this->mailer = new PHPMailer(true);
    }

    public function sendMail($to, $subject, $content, $from = "", $fromName = "")
    {
        $username = Config::get("smtp_user","mail_server");
        $host = Config::get("smtp_host","mail_server");
        $password = Config::get("smtp_pass","mail_server");
        $port = Config::get("smtp_port","mail_server");

        try {
            $this->mailer->isSMTP();
            $this->mailer->Host = $host;
            $this->mailer->SMTPAuth = true;
            $this->mailer->Username = $username;
            $this->mailer->Password = $password;
            $this->mailer->SMTPSecure = 'ssl';
            $this->mailer->Port = $port;
            $this->mailer->CharSet = "UTF-8";

            //邮件账户设置---Recipients
            $this->mailer->setFrom($username, $fromName); // 设置发件人邮箱地址与昵称
            $this->mailer->addReplyTo($username, ['Information']); // 设置回复时的用户与昵称，应与发件人相同

            // 有多个收件人时添加多个收件人，效果等同于多个$mail->addAddress()
            if(is_array($to))
            {
                foreach($to as $v)
                {
                    $this->mailer->addAddress($v);
                }
            }else{
                $this->mailer->addAddress($to);
            }

            $this->mailer->Subject = $subject;
            if(is_array($content))
            {
                $allcontent = implode(',', $content);
                $this->mailer->Body = $allcontent;
            }else{
                $this->mailer->Body = $content;
            }

            $this->mailer->send();
            return true;
        } catch (Exception $e) {
            echo "{$this->mailer->ErrorInfo}";
            return false;
        }
    }
}