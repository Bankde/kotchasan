<?php
/**
 * @filesource Kotchasan/Jwt.php
 *
 * @copyright 2016 Goragod.com
 * @license https://www.kotchasan.com/license/
 *
 * @see https://www.kotchasan.com/
 */

namespace Kotchasan;

/**
 * Class Jwt
 * A class for encoding, decoding, and verifying JSON Web Tokens (JWT).
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class Jwt
{
    /**
     * Secret key สำหรับการเข้ารหัส JWT
     * ควรกำหนดก่อนการเรียกใช้ JWT หากต้องการให้มีการ verify
     *
     * @var string
     */
    public static $secretKey = 'my_secret_key';

    /**
     * เวลาหมดอายุของ JWT
     * 3600 = 1 ชม.
     * 0 = ไม่มีวันหมดอายุ (ค่าเริ่มต้น)
     * ถ้ามีการระบุเวลาหมดอายุ เมื่อมีการ verify จะมีการตรวจสอบเวลาหมดอายุด้วย
     *
     * @var int
     */
    public static $expireTime = 0;

    /**
     * เข้ารหัส JWT อัลกอริทึม HS256
     *
     * @assert (array('name' => 'ภาษาไทย', 'id' => 1234567890)) [==] 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJuYW1lIjoiXHUwZTIwXHUwZTMyXHUwZTI5XHUwZTMyXHUwZTQ0XHUwZTE3XHUwZTIyIiwiaWQiOjEyMzQ1Njc4OTB9.fAdzmsl4AIGAyNGt7MfNum9DUIxn6DGMhdn1hw4PwwE'
     *
     * @param array $payload
     *
     * @return string
     */
    public static function encode($payload)
    {
        // Header
        $header = [
            'typ' => 'JWT',
            'alg' => 'HS256'
        ];
        // เข้ารหัส Header
        $header_encoded = static::base64UrlEncode(json_encode($header));
        // มีการระบุอายุของ JWT
        if (self::$expireTime > 0) {
            $payload['expired'] = time() + self::$expireTime;
        }
        // เข้ารหัส Payload
        $payload_encoded = static::base64UrlEncode(json_encode($payload));
        // สร้าง Signature
        $signature = static::generateSignature($header_encoded, $payload_encoded);
        // รวม Header, Payload และ Signature เข้าด้วยกัน และคืนค่า
        return $header_encoded.'.'.$payload_encoded.'.'.$signature;
    }

    /**
     * ฟังก์ชันสำหรับถอดรหัส JWT
     *
     * @assert ('eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJuYW1lIjoiXHUwZTIwXHUwZTMyXHUwZTI5XHUwZTMyXHUwZTQ0XHUwZTE3XHUwZTIyIiwiaWQiOjEyMzQ1Njc4OTB9.fAdzmsl4AIGAyNGt7MfNum9DUIxn6DGMhdn1hw4PwwE') [==] array('name' => 'ภาษาไทย', 'id' => 1234567890)
     *
     * @param string $jwt
     *
     * @return array
     */
    public static function decode($jwt)
    {
        // แยกข้อมูล JWT เป็นส่วน Header, Payload และ Signature
        $parts = explode('.', $jwt);
        // ตรวจสอบว่ามีส่วน Header, Payload และ Signature ทั้ง 3 ส่วนหรือไม่
        if (count($parts) !== 3) {
            throw new \Exception('Invalid token format');
        }
        // ถอดรหัส Payload
        $decodedPayload = static::base64UrlDecode($parts[1]);
        // คืนค่า Payload
        return json_decode($decodedPayload, true);
    }

    /**
     * ฟังก์ชันสำหรับถอดรหัส JWT และตรวจสอบความถูกต้องของข้อมูลด้วย
     * อัลกอริทึม HS256
     *
     * @assert ('eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJuYW1lIjoiXHUwZTIwXHUwZTMyXHUwZTI5XHUwZTMyXHUwZTQ0XHUwZTE3XHUwZTIyIiwiaWQiOjEyMzQ1Njc4OTB9.fAdzmsl4AIGAyNGt7MfNum9DUIxn6DGMhdn1hw4PwwE') [==] array('name' => 'ภาษาไทย', 'id' => 1234567890)
     * @assert ('eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJuYW1lIjoiXHUwZTIwXHUwZTMyXHUwZTI5XHUwZTMyXHUwZTQ0XHAwZTE3XHUwZTIyIiwiaWQiOjEyMzQ1Njc4OTB9.fAdzmsl4AIGAyNGt7MfNum9DUIxn6DGMhdn1hw4PwwE') [throws] \Exception
     * @param string $jwt
     *
     * @return array
     */
    public static function verify($jwt)
    {
        // แยกข้อมูล JWT เป็นส่วน Header, Payload และ Signature
        $parts = explode('.', $jwt);
        // ตรวจสอบว่ามีส่วน Header, Payload และ Signature ทั้ง 3 ส่วนหรือไม่
        if (count($parts) !== 3) {
            throw new \Exception('Invalid token format');
        }
        // สร้าง Signature จาก Header และ Payload ที่ได้รับมา
        $signatureExpected = self::generateSignature($parts[0], $parts[1]);
        // ตรวจสอบว่า Signature ที่ได้ตรงกับ Signature ที่อยู่ใน JWT หรือไม่
        if ($signatureExpected !== $parts[2]) {
            throw new \Exception('Invalid signature');
        }
        // ถอดรหัส Payload ด้วย base64UrlDecode()
        $decodedPayload = static::base64UrlDecode($parts[1]);
        // แปลงข้อมูลจาก JSON
        $payloadData = json_decode($decodedPayload, true);
        // ตรวจสอบว่า Payload หมดอายุหรือยัง (ถ้าระบุเวลาหมดอายุไว้)
        if (self::$expireTime > 0 && $payloadData['expired'] < time()) {
            throw new \Exception('Token has expired');
        }
        // คืนค่า Payload
        return $payloadData;
    }

    /**
     * ฟังก์ชันสร้าง Signature เข้ารหัสแบบ sha256
     *
     * @param string $headerEncoded
     * @param string $payloadEncoded
     *
     * @return string
     */
    private static function generateSignature($header, $payload)
    {
        // รวม Header และ Payload เข้าด้วยกัน
        $data = $header.'.'.$payload;
        // นำ Secret Key มาเข้ารหัสด้วยอัลกอริทึม self::$algorithm
        $signature = hash_hmac('sha256', $data, self::$secretKey, true);
        // คืนค่าข้อมูลที่เข้ารหัสแล้ว
        return static::base64UrlEncode($signature);
    }

    /**
     * ฟังก์ชันเข้ารหัสด้วย Base64
     *
     * @param string $data
     *
     * @return string
     */
    private static function base64UrlEncode($data)
    {
        // แทนที่เครื่องหมาย + ด้วย - และ / ด้วย _
        $base64 = base64_encode($data);
        $base64Url = strtr($base64, '+/', '-_');
        // ลบเครื่องหมาย = ด้านท้ายออก และคืนค่า
        return rtrim($base64Url, '=');
    }

    /**
     * ฟังก์ชันถอดรหัส base64UrlEncode
     *
     * @param string $data
     *
     * @return string
     */
    private static function base64UrlDecode($data)
    {
        // เติมเครื่องหมาย = ด้านหลังข้อมูลให้ครบตามรูปแบบของ Base64
        $data = str_pad($data, strlen($data) % 4, '=', STR_PAD_RIGHT);
        // แทนที่เครื่องหมาย - ด้วย + และ _ ด้วย /
        $base64 = strtr($data, '-_', '+/');
        // คืนค่าข้อความที่ถอดรหัสแล้ว
        return base64_decode($base64);
    }
}
