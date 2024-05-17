<?php

namespace app\common\utils;

use app\common\exceptions\ApiException;
use think\facade\Db;
use think\facade\Request;

class Util
{
    public static function getUserIp()
    {
        // 判断是否存在代理服务器IP
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            // 直接获取远程地址
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        return $ip;
    }
    /**
     * 获取用户端类型
     *
     * @return string
     */
    public static function getClientType(): string
    {
        $clinet_type = Request::header('X-Client-Type');
        switch ($clinet_type) {
            case 'pc':
                //PC端
                return 'pc';
            case 'wechat':
                //公众号
                return 'wechat';
            case 'h5':
                //h5
                return 'h5';
            case 'miniProgram':
                //小程序
                return 'miniProgram';
            default:
                return '';
        }
    }
    /**
     * 获取访问来源类型
     * @return string
     */
    public static function getUserAgent()
    {
        $userAgent = $_SERVER['HTTP_USER_AGENT'];
        if (strpos($userAgent, 'APP') !== false) {
            // 来自APP
            return 'app';
        } elseif (strpos($userAgent, 'MicroMessenger') !== false) {
            // 来自微信
            if (strpos($userAgent, 'MiniProgram') !== false) {
                // 来自微信小程序
                return 'miniProgram';
            } else {
                // 来自微信公众号
                return 'wechat';
            }
        } elseif (strpos($userAgent, 'Windows') !== false || strpos($userAgent, 'Macintosh') !== false) {
            // 来自PC
            return 'pc';
        } else {
            // 默认为H5
            return 'h5';
        }
    }
    public static function getFirstPinyin($str)
    {
        if ($str == '重庆') {
            return 'C';
        }
        $firstchar_ord = ord(strtoupper($str[0]));
        if (($firstchar_ord >= 65 and $firstchar_ord <= 91) or ($firstchar_ord >= 48 and $firstchar_ord <= 57)) {
            return $str[0];
        }

        $s = iconv("UTF-8", "gb2312", $str);
        $asc = ord($s[0]) * 256 + ord($s[1]) - 65536;
        if ($asc >= -20319 and $asc <= -20284) {
            return "A";
        }

        if ($asc >= -20283 and $asc <= -19776) {
            return "B";
        }

        if ($asc >= -19775 and $asc <= -19219) {
            return "C";
        }

        if ($asc >= -19218 and $asc <= -18711) {
            return "D";
        }

        if ($asc >= -18710 and $asc <= -18527) {
            return "E";
        }

        if ($asc >= -18526 and $asc <= -18240) {
            return "F";
        }

        if ($asc >= -18239 and $asc <= -17923) {
            return "G";
        }

        if ($asc >= -17922 and $asc <= -17418) {
            return "H";
        }

        if ($asc >= -17417 and $asc <= -16475) {
            return "J";
        }

        if ($asc >= -16474 and $asc <= -16213) {
            return "K";
        }

        if ($asc >= -16212 and $asc <= -15641) {
            return "L";
        }

        if ($asc >= -15640 and $asc <= -15166) {
            return "M";
        }

        if ($asc >= -15165 and $asc <= -14923) {
            return "N";
        }

        if ($asc >= -14922 and $asc <= -14915) {
            return "O";
        }

        if ($asc >= -14914 and $asc <= -14631) {
            return "P";
        }

        if ($asc >= -14630 and $asc <= -14150) {
            return "Q";
        }

        if ($asc >= -14149 and $asc <= -14091) {
            return "R";
        }

        if ($asc >= -14090 and $asc <= -13319) {
            return "S";
        }

        if ($asc >= -13318 and $asc <= -12839) {
            return "T";
        }

        if ($asc >= -12838 and $asc <= -12557) {
            return "W";
        }

        if ($asc >= -12556 and $asc <= -11848) {
            return "X";
        }

        if ($asc >= -11847 and $asc <= -11056) {
            return "Y";
        }

        if ($asc >= -11055 and $asc <= -10247) {
            return "Z";
        }

        return null;
    }

    /**
     * 过滤用户输入的基本数据，防止script攻击
     *
     * @access      public
     * @return      string
     */
    public static function compile_str($str)
    {
        $arr = array('<' => '＜', '>' => '＞', '"' => '”', "'" => '’');

        return strtr($str, $arr);
    }

    /**
     * 发送邮件
     * @param array $data
     * @return array|bool
     * @throws ApiException
     */
    public static function sendEmail(array $data)
    {
        // 是否开启消息队列
        if (Config::get("is_open_queue")) {
            return \QueueApi::push(['type' => 'notice.send_email', 'data' => $data]);
        }
        /* 如果邮件编码不是UTF8，创建字符集转换对象，转换编码 */
        $charset = Config::get("mail_charset", "mail_server");
        $smtp_mail = Config::get("smtp_mail", "mail_server");
        $shop_name = Config::get("shop_name");
        if ($charset != "UTF8") {
            $data["name"] = mb_convert_encoding($data["name"], "UTF-8", $charset);
            $data["subject"] = mb_convert_encoding($data["subject"], "UTF-8", $charset);
            $data["content"] = mb_convert_encoding($data["content"], "UTF-8", $charset);
            $shop_name = mb_convert_encoding($shop_name, "UTF-8", $charset) ?? "";
        }

        $mail_service = Config::get("mail_service", "mail_server");
        $notification = (isset($data['notification']) && !empty($data['notification'])) ? $data['notification'] : false;
        if ($mail_service == 0) {
            // 使用mail函数发送邮件
            $content_type = ($data["type"] == 0) ? 'Content-Type: text/plain; charset=' . $charset : 'Content-Type: text/html; charset=' . $charset;
            $headers = array();
            $headers[] = 'From: "' . '=?' . $charset . '?B?' . base64_encode($shop_name) . '?=' . '" <' . $smtp_mail . '>';
            $headers[] = $content_type . '; format=flowed';
            if ($notification) {
                $headers[] = 'Disposition-Notification-To: ' . '=?' . $charset . '?B?' . base64_encode($shop_name) . '?=' . '" <' . $smtp_mail . '>';
            }
            $result = @mail($data['email'], '=?' . $charset . '?B?' . base64_encode($data['subject']) . '?=', $data['content'], implode("\r\n", $headers));

        } else {
            // 使用smtp服务发送邮件
            $host = Config::get("smtp_host", "mail_server");
            $port = Config::get("smtp_port", "mail_server");
            if (empty($host) || empty($port)) {
                throw new ApiException('邮件服务器设置信息不完整');
            }
            if (!function_exists('fsockopen')) {
                throw new ApiException('服务器已禁用 fsocketopen 函数。');
            }

            $mailer = new PHPMailerWrapper();
            $to = $data['email'];
            $subject = $data['subject'];
            $content = $data['content'];

            //设置邮件编码
            mb_language('uni');
            mb_internal_encoding('UTF-8');

            $result = $mailer->sendMail($to, $subject, $content);
        }

        if (!$result) {
            self::addMailLog($data['email'], $data['subject']);
            throw new ApiException('邮件发送失败，请检查您的邮件服务器设置！');
        }

        return $result;
    }

    // 添加发送邮件日志
    public static function addMailLog(string $email = "", string $content = "", int $status = 0)
    {
        $arr = [
            "user_id" => request()->userId ?? 0,
            "email" => $email,
            "send_time" => Time::now(),
            "status" => $status,
            "content" => Util::compile_str($content),
        ];
        Db::name("mail_log")->insert($arr);
    }

    /**
     * 递归生成目录
     * @param string $path
     * @return void
     */
    public static function createFolder(string $path): void
    {
        if (!is_dir($path)) {
            self::createFolder(dirname($path));
            mkdir($path);
        }
    }

    /**
     * 计算转换格式
     * @param int|float $data
     * @param int $decimals
     * @param string $dec_point
     * @return string
     */
    public static function number_format_convert(int | float $data, int $decimals = 2, string $dec_point = '.')
    {
        return number_format($data, $decimals, $dec_point, '');
    }


    /**
     * 验证手机号
     * @param int $mobile
     * @return bool
     */
    public static function validateMobile(int|string $mobile): bool
    {
        if (!preg_match("/^1[3456789]{1}\d{9}$/", $mobile)) {
            return false;
        }
        return true;
    }
}
