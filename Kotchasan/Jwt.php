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
 * คลาสสำหรับการเข้ารหัส ถอดรหัส JWT และมีการ verify ด้วย
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class Jwt
{
    /**
     * Secret key สำหรับการเข้ารหัส JWT
     *
     * @var string
     */
    private $secretKey;

    /**
     * เวลาหมดอายุของ JWT
     * 3600 = 1 ชม.
     * 0 = ไม่มีวันหมดอายุ (ค่าเริ่มต้น)
     * ถ้ามีการระบุเวลาหมดอายุ เมื่อมีการ verify จะมีการตรวจสอบเวลาหมดอายุด้วย
     * โดยจะมีการเพิ่มข้อมูล expired เพื่อเก็บเวลาหมดอายุ ลงใน Payload โดยอัตโนมัติ
     * และลบออกเมื่อมีการถอดรหัสกลับ ไม่ควรกำหนด expired ลงใน Payload ที่ต้องการเข้ารหัสด้วยตัวเอง
     *
     * @var int
     */
    private $expireTime;

    /**
     * อัลกอริทึมที่ใช้ในการเข้ารหัส ด้วย hash_hmac
     *
     * @var string
     */
    private $algorithm;

    /**
     * อัลกอริทึมที่รองรับโดย hash_hmac
     *
     * @var array
     */
    protected $hash_hmac_algos = array(
        'HS256' => 'sha256',
        'HS384' => 'sha384',
        'HS512' => 'sha512'
    );

    /**
     * Class constructor
     *
     * @param string $secretKey Secret key สำหรับการเข้ารหัส JWT
     * @param int $expireTime เวลาหมดอายุของ JWT 0 = ไม่มีวันหมดอายุ (ค่าเริ่มต้น), > 0 กำหนดเวลาหมดอายุเป็นวินาที
     * @param int $algo อัลกอริทึมที่ใช้ในการเข้ารหัส รองรับตาม $hash_hmac_algos
     */
    private function __construct($secretKey, $expireTime, $algo)
    {
        if (isset($this->hash_hmac_algos[$algo])) {
            $this->algorithm = $algo;
        } else {
            throw new \Exception('Algorithm `'.$algo.'` not support');
        }
        $this->secretKey = $secretKey;
        $this->expireTime = $expireTime;
    }

    /**
     * สร้างคลาส JWT
     *
     * @param string $secretKey Secret key สำหรับการเข้ารหัส JWT
     * @param int $expireTime เวลาหมดอายุของ JWT 0 = ไม่มีวันหมดอายุ (ค่าเริ่มต้น), > 0 กำหนดเวลาหมดอายุเป็นวินาที
     *
     * @return static
     */
    public static function create($secretKey = 'my_secret_key', $expireTime = 0, $algo = 'HS256')
    {
        return new static($secretKey, $expireTime, $algo);
    }

    /**
     * เข้ารหัส JWT อัลกอริทึม HS256
     *
     * @assert (array('name' => 'ภาษาไทย', 'id' => 1234567890)) [==] 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJuYW1lIjoiXHUwZTIwXHUwZTMyXHUwZTI5XHUwZTMyXHUwZTQ0XHUwZTE3XHUwZTIyIiwiaWQiOjEyMzQ1Njc4OTB9.fAdzmsl4AIGAyNGt7MfNum9DUIxn6DGMhdn1hw4PwwE'
     *
     * @param array $payload
     *
     * @return string
     */
    public function encode($payload)
    {
        // Header
        $header = [
            'typ' => 'JWT',
            'alg' => $this->algorithm
        ];
        // เข้ารหัส Header
        $header_encoded = $this->base64UrlEncode(json_encode($header));
        // มีการระบุอายุของ JWT
        if ($this->expireTime > 0) {
            $payload['expired'] = time() + $this->expireTime;
        }
        // เข้ารหัส Payload
        $payload_encoded = $this->base64UrlEncode(json_encode($payload));
        // สร้าง Signature
        $signature = $this->generateSignature($header_encoded, $payload_encoded);
        // รวม Header, Payload และ Signature เข้าด้วยกัน และคืนค่า
        return "$header_encoded.$payload_encoded.$signature";
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
    public function decode($jwt)
    {
        // แยกข้อมูล JWT เป็นส่วน Header, Payload และ Signature
        $parts = explode('.', $jwt);
        // ตรวจสอบว่ามีส่วน Header, Payload และ Signature ทั้ง 3 ส่วนหรือไม่
        if (count($parts) !== 3) {
            throw new \Exception('Invalid token format');
        }
        // ถอดรหัส Payload
        $decodedPayload = $this->base64UrlDecode($parts[1]);
        // แปลงข้อมูลจาก JSON
        $payloadData = json_decode($decodedPayload, true);
        // มีการระบุอายุของ JWT ลบเวลาหมดอายุออก
        if ($this->expireTime > 0) {
            unset($payloadData['expired']);
        }
        // คืนค่า Payload
        return $payloadData;
    }

    /**
     * ฟังก์ชันสำหรับถอดรหัส JWT และตรวจสอบความถูกต้องของข้อมูลด้วย
     * อัลกอริทึม HS256
     *
     * @assert ('eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJuYW1lIjoiXHUwZTIwXHUwZTMyXHUwZTI5XHUwZTMyXHUwZTQ0XHUwZTE3XHUwZTIyIiwiaWQiOjEyMzQ1Njc4OTB9.fAdzmsl4AIGAyNGt7MfNum9DUIxn6DGMhdn1hw4PwwE') [==] array('name' => 'ภาษาไทย', 'id' => 1234567890)
     * @assert ('eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJuYW1lIjoiXHUwZTIwXHUwZTMyXHUwZTI5XHUwZTMyXHUwZTQ0XHAwZTE3XHUwZTIyIiwiaWQiOjEyMzQ1Njc4OTB9.fAdzmsl4AIGAyNGt7MfNum9DUIxn6DGMhdn1hw4PwwE') [throws] \Exception
     *
     * @param string $jwt
     *
     * @return array
     */
    public function verify($jwt)
    {
        // แยกข้อมูล JWT เป็นส่วน Header, Payload และ Signature
        $parts = explode('.', $jwt);
        // ตรวจสอบว่ามีส่วน Header, Payload และ Signature ทั้ง 3 ส่วนหรือไม่
        if (count($parts) !== 3) {
            throw new \Exception('Invalid token format');
        }
        // สร้าง Signature จาก Header และ Payload ที่ได้รับมา
        $signatureExpected = $this->generateSignature($parts[0], $parts[1]);
        // ตรวจสอบว่า Signature ที่ได้ตรงกับ Signature ที่อยู่ใน JWT หรือไม่
        if ($signatureExpected !== $parts[2]) {
            throw new \Exception('Invalid signature');
        }
        // ถอดรหัส Payload ด้วย base64UrlDecode()
        $decodedPayload = $this->base64UrlDecode($parts[1]);
        // แปลงข้อมูลจาก JSON
        $payloadData = json_decode($decodedPayload, true);
        if ($this->expireTime > 0) {
            // ตรวจสอบว่า Payload หมดอายุหรือยัง (ถ้าระบุเวลาหมดอายุไว้)
            if ($payloadData['expired'] < time()) {
                throw new \Exception('Token has expired');
            }
            // ลบเวลาหมดอายุออก
            unset($payload['expired']);
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
    private function generateSignature($header, $payload)
    {
        // นำ Secret Key มาเข้ารหัส
        $signature = hash_hmac($this->hash_hmac_algos[$this->algorithm], "$header.$payload", $this->secretKey, true);
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
    private function base64UrlEncode($data)
    {
        // แทนที่เครื่องหมาย + ด้วย - และ / ด้วย _
        $base64Url = strtr(base64_encode($data), '+/', '-_');
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
    private function base64UrlDecode($data)
    {
        // เติมเครื่องหมาย = ด้านหลังข้อมูลให้ครบตามรูปแบบของ Base64
        $data = str_pad($data, strlen($data) % 4, '=', STR_PAD_RIGHT);
        // แทนที่เครื่องหมาย - ด้วย + และ _ ด้วย / และคืนค่า
        return base64_decode(strtr($data, '-_', '+/'));
    }
}
