<?php
/**
 * User: xiangzhiping
 * Date: 2018/7/5
 */

namespace App\Component;

use App\Exceptions\JwtException;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\JWT;

class JwtToken
{
    // 私钥：
    private static $singleInstance;

    private function __construct()
    {
        JWT::$leeway = 60;// 缓冲时间 秒
    }

    // 单例处理
    public static function instance()
    {
        if (!self::$singleInstance)
            self::$singleInstance = new self;

        return self::$singleInstance;
    }

    /**
     * 生成TOKEN
     *
     * @param array $data
     *
     * @return string
     * @throws
     */
    public function generateToken(array $data)
    {
        if (!is_array($data))
            throw new JwtException('数据格式不正确');
        $now        = time();
        $token      = [
            "aud" => config('app.url'),
            "nbf" => $now, // 最晚使用的时间  也就是过期时间
            "exp" => $now + 7 * 24 * 3600, // 过期时间
        ];
        $token      = array_merge($token, $data);
        $algorithms = self::allowAlgorithms();
        shuffle($algorithms);
        $alg = array_shift($algorithms);
        $key = self::getGenerateKeyByAlg($alg);
        try {
            $jwt = JWT::encode($token, $key, $alg);
        } catch (\Exception $e) {
            throw new JwtException('token generate failed: ' . $e->getMessage(), 101);
        }

        return $jwt;
    }

    /**
     * 解析token数据
     *
     * @param $token
     *
     * @return array
     *
     * @throws
     */
    public function parseToken($token)
    {
        if (!$token) {
            throw new JwtException('token is empty');
        }

        try {
            $key          = self::getParseKeyByAlg($token);
            $payload_data = JWT::decode($token, $key, self::allowAlgorithms());
            $payload_data = (array)$payload_data;
        } catch (ExpiredException $e) {
            throw new JwtException('token is expired, please login again', 101404);
        } catch (\Exception $e) {
            throw new JwtException('token is invalid' . $e->getMessage(), 101404);
        }

        return $payload_data;
    }

    /**
     * 获取解析秘钥
     *
     * @param string $token
     *
     * @return string
     *
     * @throws
     */
    private static function getParseKeyByAlg($token)
    {
        $tks = explode('.', $token);
        if (count($tks) != 3) {
            throw new \UnexpectedValueException('Wrong number of segments');
        }
        list($headb64,) = $tks;
        if (null === ($header = JWT::jsonDecode(JWT::urlsafeB64Decode($headb64)))) {
            throw new \UnexpectedValueException('Invalid header encoding');
        }
        if (empty($header->alg)) {
            throw new \UnexpectedValueException('Empty algorithm');
        }
        list($function,) = JWT::$supported_algs[$header->alg];
        switch ($function) {
            case 'hash_hmac':
                return config('jwt.JWT_KEY', '');
            case 'openssl':
                return self::getPublicKey(config('jwt.JWT_PUBLIC_KEY', ''));
        }

        throw new JwtException('no key available');
    }

    /**
     * 获取生成秘钥
     */
    private static function getGenerateKeyByAlg($alg)
    {
        list($function,) = JWT::$supported_algs[$alg];

        switch ($function) {
            case 'hash_hmac':
                return config('jwt.JWT_KEY', '');
            case 'openssl':
                return self::getPrivateKey(config('jwt.JWT_PRIVATE_KEY', ''));
        }

        throw new JwtException('no key available');
    }

    private static function getPrivateKey($private_key)
    {
        if (!$private_key) {
            throw new JwtException('no private_key supplied');
        }

        $start       = '-----BEGIN RSA PRIVATE KEY-----';
        $end         = '-----END RSA PRIVATE KEY-----';
        $private_key = str_replace([$start, $end, "\n", "\r"], "", $private_key);

        return $start . "\n" . wordwrap($private_key, 64, "\n", true) . "\n" . $end;
    }

    private static function getPublicKey($publicKey)
    {
        if (!$publicKey) {
            throw new JwtException('no private_key supplied');
        }
        $start     = '-----BEGIN PUBLIC KEY-----';
        $end       = '-----END PUBLIC KEY-----';
        $publicKey = str_replace([$start, $end, "\n", "\r"], "", $publicKey);

        return $start . "\n" . wordwrap($publicKey, 64, "\n", true) . "\n" . $end;
    }

    // 允许使用的算法
    private static function allowAlgorithms()
    {
        return ($allow = config('jwt.JWT_ALLOW_ALGORITHMS')) ? implode(',', $allow) : array_keys(JWT::$supported_algs);
    }
}


