<?php
/**
 * Created by PhpStorm.
 * User: Bruno
 * Date: 16/12/2018
 * Time: 19:55
 */

namespace App;

require '../vendor/autoload.php';

use App\Entity\User;
use Firebase\JWT\JWT;
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

    public function createToken(User $user)
    {
        $time = time();
        $token = array(
            'iat' => $time,
            'exp' => $time + (24*60*60),
            'data' => [
                'id' => $user->getId(),
                'isAdmin' => $user->isAdmin()
            ]
        );
        return JWT::encode($token,$this->key);
    }

    public function get()
    {
        return $this->key;
    }
}