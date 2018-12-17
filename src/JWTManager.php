<?php
/**
 * Created by PhpStorm.
 * User: Bruno
 * Date: 16/12/2018
 * Time: 19:55
 */

namespace App;

use App\Entity\User;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\JWT;
use Firebase\JWT\SignatureInvalidException;
use Symfony\Component\Dotenv\Dotenv;

class JWTManager
{
    private $key;

    public function __construct()
    {
        $dotenv = new Dotenv();
        $dotenv->load('../.env');
        $this->key = getenv('JWT_KEY');
    }

    /**
     * @param User $user
     * @return string
     */
    public function createToken(User $user): string
    {
        $time = time();
        $token = array(
            'iat' => $time,
            'exp' => $time + (24 * 60 * 60),
            'data' => [
                'id' => $user->getId(),
                'isAdmin' => $user->isAdmin()
            ]
        );
        return JWT::encode($token, $this->key);
    }

    /**
     * @param string $token
     * @return bool
     */
    public function isValidToken(string $token): bool
    {
        try {
            $data = JWT::decode($token, $this->key, array('HS256'));
        } catch (SignatureInvalidException $exception) {
            return false;
        } catch (ExpiredException $exception) {
            return false;
        }
        return true;
    }

    public function isAdminUser(string $token)
    {
        $data = JWT::decode($token, $this->key, array('HS256'));
        $data->data->id;
        $data->data->isAdmin;
        return $data->data->isAdmin;
    }
}