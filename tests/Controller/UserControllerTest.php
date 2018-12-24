<?php
/**
 * Created by PhpStorm.
 * User: Bruno
 * Date: 21/12/2018
 * Time: 19:29
 */

namespace App\Tests\Controller;


use App\Controller\LoginController;
use App\Controller\UserController;
use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class UserControllerTest
 * @package App\Tests\Controller
 * @coversDefaultClass \App\Controller\UserController
 */
class UserControllerTest extends WebTestCase
{
    /** @var Client */
    private static $client;
    private static $token;
    private static $notAdminToken;
    private static $createdUserId;

    public static function setUpBeforeClass()
    {
        self::$client = static::createClient();
        self::$client->request(Request::METHOD_POST,UserController::USER_API_PATH.'setUpTests');
        self::$client->request(Request::METHOD_POST, LoginController::LOGIN_API_PATH, array(), array(),
            array('CONTENT_TYPE' => 'application/json'), '{"_username":"bruno","_password":"aaa"}');
        self::$token = json_decode(self::$client->getResponse()->getContent())->token;
        self::$client->request(Request::METHOD_POST, LoginController::LOGIN_API_PATH, array(), array(),
            array('CONTENT_TYPE' => 'application/json'), '{"_username":"aaa","_password":"aaa"}');
        self::$notAdminToken = json_decode(self::$client->getResponse()->getContent())->token;
    }

    /**
     * @covers ::optionsUser
     */
    public function testOptionsUser()
    {
        self::$client->request(Request::METHOD_OPTIONS, UserController::USER_API_PATH);
        self::assertEquals(200, self::$client->getResponse()->getStatusCode());
        $headers = self::$client->getResponse()->headers->all();
        self::assertArrayHasKey('allow', $headers);
        self::assertEquals('GET, POST', $headers['allow'][0]);
    }

    /**
     * @covers ::optionsOneUser
     */
    public function testOptionsOneUser()
    {
        self::$client->request(Request::METHOD_OPTIONS, UserController::USER_API_PATH . '/1');
        self::assertEquals(200, self::$client->getResponse()->getStatusCode());
        $headers = self::$client->getResponse()->headers->all();
        self::assertArrayHasKey('allow', $headers);
        self::assertEquals('GET, PUT, DELETE', $headers['allow'][0]);
    }

    /**
     * @covers ::postUser
     */
    public function testPostUser()
    {
        self::$client->request(Request::METHOD_POST, UserController::USER_API_PATH, array(), array(),
            array('CONTENT_TYPE' => 'application/json', 'HTTP_token' => self::$token),
            '{"email":"test@test.com","password":"test","enabled":true,"isAdmin":true}');
        self::assertEquals(422, self::$client->getResponse()->getStatusCode());

        self::$client->request(Request::METHOD_POST, UserController::USER_API_PATH, array(), array(),
            array('CONTENT_TYPE' => 'application/json', 'HTTP_token' => self::$token),
            '{"username":"bruno","email":"test@test.com","password":"test","enabled":true,"isAdmin":true}');
        self::assertEquals(400, self::$client->getResponse()->getStatusCode());

        self::$client->request(Request::METHOD_POST, UserController::USER_API_PATH, array(), array(),
            array('CONTENT_TYPE' => 'application/json', 'HTTP_token' => self::$token),
            '{"username":"test","email":"test@test.com","password":"test","enabled":true,"isAdmin":true}');
        self::assertEquals(201, self::$client->getResponse()->getStatusCode());

        self::$createdUserId = json_decode(self::$client->getResponse()->getContent())->user->id;
    }

    /**
     * @covers ::getCUser
     * @depends testPostUser
     */
    public function testGetCUser()
    {
        self::$client->request(Request::METHOD_GET, UserController::USER_API_PATH, array(), array(),
            array('HTTP_token' => self::$token));
        self::assertEquals(200, self::$client->getResponse()->getStatusCode());
        $body = self::$client->getResponse()->getContent();
        self::assertJson($body);
        self::assertObjectHasAttribute('users', json_decode($body));
    }

    /**
     * @covers ::getOneUser
     * @depends testPostUser
     */
    public function testGetUser()
    {
        self::$client->request(Request::METHOD_GET, UserController::USER_API_PATH . '/' . (-1), array(), array(),
            array('HTTP_token' => self::$token));
        self::assertEquals(404, self::$client->getResponse()->getStatusCode());

        self::$client->request(Request::METHOD_GET, UserController::USER_API_PATH . '/' . self::$createdUserId, array(), array(),
            array('HTTP_token' => self::$token));
        self::assertEquals(200, self::$client->getResponse()->getStatusCode());
        $body = json_decode(self::$client->getResponse()->getContent());
        self::assertObjectHasAttribute('user', $body);
        self::assertEquals(self::$createdUserId, $body->user->id);
        self::assertEquals('test', $body->user->username);
    }

    /**
     * @covers ::putUser
     * @depends testGetUser
     */
    public function testPutUser()
    {
        self::$client->request(Request::METHOD_PUT, UserController::USER_API_PATH . '/' . (-1), array(), array(),
            array('CONTENT_TYPE' => 'application/json', 'HTTP_token' => self::$token),
            '{"username":"test2","email":"test2@test2.com","password":"test","enabled":false,"isAdmin":false}');
        self::assertEquals(404, self::$client->getResponse()->getStatusCode());

        self::$client->request(Request::METHOD_PUT, UserController::USER_API_PATH . '/' . self::$createdUserId, array(), array(),
            array('CONTENT_TYPE' => 'application/json', 'HTTP_token' => self::$token),
            '{"username":"bruno","email":"test2@test2.com","password":"test","enabled":false,"isAdmin":false}');
        self::assertEquals(400, self::$client->getResponse()->getStatusCode());

        self::$client->request(Request::METHOD_PUT, UserController::USER_API_PATH . '/' . self::$createdUserId, array(), array(),
            array('CONTENT_TYPE' => 'application/json', 'HTTP_token' => self::$token),
            '{"username":"test2","email":"bruno@bruno.com","password":"test","enabled":false,"isAdmin":false}');
        self::assertEquals(400, self::$client->getResponse()->getStatusCode());

        self::$client->request(Request::METHOD_PUT, UserController::USER_API_PATH . '/' . self::$createdUserId, array(), array(),
            array('CONTENT_TYPE' => 'application/json', 'HTTP_token' => self::$token),
            '{"username":"test2","email":"test2@test2.com","password":"test","enabled":false,"isAdmin":false}');
        self::assertEquals(209, self::$client->getResponse()->getStatusCode());
        $body = json_decode(self::$client->getResponse()->getContent());
        self::assertEquals('test2',$body->user->username);
    }

    /**
     * @covers ::deleteUser
     * @depends testPutUser
     */
    public function testDeleteUser()
    {
        self::$client->request(Request::METHOD_DELETE, UserController::USER_API_PATH . '/' . (-1), array(), array(),
            array('HTTP_token' => self::$token));
        self::assertEquals(404, self::$client->getResponse()->getStatusCode());

        self::$client->request(Request::METHOD_DELETE, UserController::USER_API_PATH . '/' . self::$createdUserId, array(), array(),
            array('HTTP_token' => self::$token));
        self::assertEquals(204, self::$client->getResponse()->getStatusCode());

        self::$client->request(Request::METHOD_GET, UserController::USER_API_PATH . '/' . self::$createdUserId, array(), array(),
            array('HTTP_token' => self::$token));
        self::assertEquals(404, self::$client->getResponse()->getStatusCode());
    }

    /**
     * @covers ::authorization
     */
    public function testAuthorization()
    {
        self::$client->request(Request::METHOD_GET, UserController::USER_API_PATH);
        $body = self::$client->getResponse()->getContent();
        self::$client->getResponse()->getStatusCode();
        self::assertEquals(401, self::$client->getResponse()->getStatusCode());
        self::assertJson($body);
        $data = json_decode($body);
        self::assertObjectHasAttribute('message', $data);
        self::assertEquals('Unauthorized invalid token header', $data->message);

        self::$client->request(Request::METHOD_GET, UserController::USER_API_PATH, array(), array(), array(
            'HTTP_token' => self::$notAdminToken
        ));
        self::assertEquals(403, self::$client->getResponse()->getStatusCode());
        self::assertObjectHasAttribute('message', json_decode(self::$client->getResponse()->getContent()));
        self::assertEquals('Forbidden You don\'t have permission to access',
            json_decode(self::$client->getResponse()->getContent())->message);

        self::$client->request(Request::METHOD_GET, UserController::USER_API_PATH, array(), array(), array(
            'HTTP_token' => self::$token
        ));
        self::assertEquals(200, self::$client->getResponse()->getStatusCode());
    }
}