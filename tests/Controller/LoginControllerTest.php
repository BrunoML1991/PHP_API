<?php
/**
 * Created by PhpStorm.
 * User: Bruno
 * Date: 23/12/2018
 * Time: 18:08
 */

namespace App\Tests\Controller;


use App\Controller\LoginController;
use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class LoginControllerTest
 * @package App\Tests\Controller
 * @coversDefaultClass \App\Controller\LoginController
 */
class LoginControllerTest extends WebTestCase
{

    /** @var Client */
    private static $client;

    public static function setUpBeforeClass()
    {
        self::$client = static::createClient();
    }

    /**
     * @covers ::postLogin
     */
    public function testPostLogin()
    {
        self::$client->request(Request::METHOD_POST,LoginController::LOGIN_API_PATH,array(),array(),
            array('CONTENT_TYPE'=>'application/json'),'{"_rname":"bruno","_password":"aaa"}');
        self::assertEquals(401,self::$client->getResponse()->getStatusCode());

        self::$client->request(Request::METHOD_POST,LoginController::LOGIN_API_PATH,array(),array(),
            array('CONTENT_TYPE'=>'application/json'),'{"_username":"brun","_password":"aaa"}');
        self::assertEquals(401,self::$client->getResponse()->getStatusCode());

        self::$client->request(Request::METHOD_POST,LoginController::LOGIN_API_PATH,array(),array(),
            array('CONTENT_TYPE'=>'application/json'),'{"_username":"bruno","_password":"aa"}');
        self::assertEquals(401,self::$client->getResponse()->getStatusCode());

        self::$client->request(Request::METHOD_POST,LoginController::LOGIN_API_PATH,array(),array(),
            array('CONTENT_TYPE'=>'application/json'),'{"_username":"bruno","_password":"aaa"}');
        self::assertEquals(200,self::$client->getResponse()->getStatusCode());
        self::assertObjectHasAttribute('token',json_decode(self::$client->getResponse()->getContent()));
    }

}