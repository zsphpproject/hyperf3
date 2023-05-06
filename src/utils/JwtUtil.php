<?php

namespace Zsgogo\utils;

use Firebase\JWT\ExpiredException;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Hyperf\Stringable\Str;
use League\Flysystem\Filesystem;
use App\common\constant\ErrorNums;
use Zsgogo\exception\AppException;

class JwtUtil {
    /**
     * @var string $privateKey
     */
    private $privateKey;

    /**
     * @var string $publicKey
     */
    private $publicKey;

    /**
     * @var int $validity
     */
    private $validity;

    /**
     * @var bool $multiLogin
     */
    private $multiLogin = true; // 支持多人同时登录



    public function __construct(Filesystem $filesystem,array $config = []) {
        if (empty($config)) $config = config("jwt");
        $this->privateKey = $filesystem->read($config["privateKey"]);
        $this->publicKey = $filesystem->read($config["publicKey"]);
        $this->validity = $config["validity"];
    }


    /**
     * 生成token
     * @param array $data
     * @return array
     */
    public function encode(array $data): array {
        $now = time();
        $expiresTime = ($now + $this->validity);
        $data["group_id"] = Str::random(24);
        $payload = [
            'iat' => $now,
            'exp' => $expiresTime,
            "data" => $data
        ];
        $encode = JWT::encode($payload, $this->privateKey, 'RS256');

        // redis缓存group_id
        if (!$this->multiLogin) $this->saveToken($data);

        return [
            "access_token" => $encode,
            "express_in" => (int)$this->validity,
            "token_type" => "Bearer"
        ];
    }


    /**
     * @param string $jwt
     * @return mixed
     * @throws AppException
     */
    public function decode(string $jwt): mixed {
        try {
            $token = $this->getTypeToken($jwt);
            $strClass = JWT::decode($token, new Key($this->publicKey, 'RS256'));
            $payload = json_decode(json_encode($strClass),true);
            if (!$this->multiLogin) $this->checkGroupId($payload);
            return $payload;
        }catch (ExpiredException $e){
            throw new AppException(ErrorNums::INVALID_AUTH,"token过期");
        }catch (\Exception $e){
            throw new AppException(ErrorNums::INVALID_AUTH,"error:".$e->getMessage());
        }

    }


    /**
     * @param string $token
     * @return string
     * @throws AppException
     */
    public function getTypeToken(string $token): string {
        $contains = Str::contains($token, ["Bearer", "bearer"]);
        if (!$contains) throw new AppException(ErrorNums::INVALID_AUTH,"token type error");
        return trim(str_replace("Bearer", "", $token));
    }


    /**
     * 判断group_id,如果token中的group_id和redis缓存中的group_id不符,则代表当前的group_id失效（客户端获取了新的token）
     * @param array $payload
     * @return void
     * @throws AppException
     */
    private function checkGroupId(array $payload){
        // if (!isset($payload["data"]["group_id"]) || !isset($payload["data"]["admin_user_id"])) throw new AppException(ErrorNums::INVALID_AUTH);
        // $saveGroupId = (new RedisUtil())->get(Common::ADMIN_USER_GROUP_ID_TAG . $payload["data"]["admin_user_id"]);
        // if ($saveGroupId !== $payload["data"]["group_id"]) throw new AppException(ErrorNums::INVALID_AUTH,"token失效");
    }


    /**
     * 把group_id 存到redis中
     * @param array $data
     * @return void
     */
    private function saveToken(array $data) {
        // (new RedisUtil())->set(Common::ADMIN_USER_GROUP_ID_TAG . $data["admin_user_id"],$data["group_id"],$this->validity);
    }

    /**
     * 删除group_id
     * @param int $user_id
     * @return void
     */
    public function deleteGroup(int $user_id): void {
        // (new RedisUtil())->delete(Common::ADMIN_USER_GROUP_ID_TAG . $user_id);
    }
}