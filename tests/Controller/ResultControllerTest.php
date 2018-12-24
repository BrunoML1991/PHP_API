<?php
/**
 * Created by PhpStorm.
 * User: Bruno
 * Date: 24/12/2018
 * Time: 19:29
 */

namespace App\Tests\Controller;


use App\Controller\LoginController;
use App\Controller\ResultController;
use App\Controller\UserController;
use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class ResultControllerTest
 * @package App\Tests\Controller
 * @coversDefaultClass \App\Controller\ResultController
 */
class ResultControllerTest extends WebTestCase
{
    /** @var Client */
    private static $client;
    private static $token;
    private static $notAdminToken;
    private static $createdUserId;
    private static $createdResultId;

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
     * @covers ::optionsResult
     */
    public function testOptionsResult()
    {
        self::$client->request(Request::METHOD_OPTIONS, ResultController::RESULT_API_PATH);
        self::assertEquals(200, self::$client->getResponse()->getStatusCode());
        $headers = self::$client->getResponse()->headers->all();
        self::assertArrayHasKey('allow', $headers);
        self::assertEquals('GET, POST', $headers['allow'][0]);
    }

    /**
     * @covers ::optionsOneResult
     */
    public function testOptionsOneResult()
    {
        self::$client->request(Request::METHOD_OPTIONS, ResultController::RESULT_API_PATH . '/1');
        self::assertEquals(200, self::$client->getResponse()->getStatusCode());
        $headers = self::$client->getResponse()->headers->all();
        self::assertArrayHasKey('allow', $headers);
        self::assertEquals('GET, PUT, DELETE', $headers['allow'][0]);
    }

    /**
     * @covers ::postResult
     */
    public function testPostResult()
    {
        self::$client->request(Request::METHOD_POST, UserController::USER_API_PATH, array(), array(),
            array('CONTENT_TYPE' => 'application/json', 'HTTP_token' => self::$token),
            '{"username":"testResult","email":"testResult@test.com","password":"test","enabled":true,"isAdmin":true}');
        self::$createdUserId = json_decode(self::$client->getResponse()->getContent())->user->id;

        self::$client->request(Request::METHOD_POST, ResultController::RESULT_API_PATH, array(), array(),
            array('CONTENT_TYPE' => 'application/json', 'HTTP_token' => self::$token),
            '{}');
        self::assertEquals(422, self::$client->getResponse()->getStatusCode());

        self::$client->request(Request::METHOD_POST, ResultController::RESULT_API_PATH, array(), array(),
            array('CONTENT_TYPE' => 'application/json', 'HTTP_token' => self::$token),
            '{"result":7,"user_id":' . (-1) . '}');
        self::assertEquals(404, self::$client->getResponse()->getStatusCode());

        self::$client->request(Request::METHOD_POST, ResultController::RESULT_API_PATH, array(), array(),
            array('CONTENT_TYPE' => 'application/json', 'HTTP_token' => self::$token),
            '{"result":7,"user_id":' . self::$createdUserId . '}');
        self::assertEquals(201, self::$client->getResponse()->getStatusCode());

        self::$createdResultId = json_decode(self::$client->getResponse()->getContent())->result->id;
    }

    /**
     * @covers ::getCResult
     * @depends testPostResult
     */
    public function testGetCResult()
    {
        self::$client->request(Request::METHOD_GET, ResultController::RESULT_API_PATH, array(), array(),
            array('HTTP_token' => self::$token));
        self::assertEquals(200, self::$client->getResponse()->getStatusCode());
        $body = self::$client->getResponse()->getContent();
        self::assertJson($body);
        self::assertObjectHasAttribute('results', json_decode($body));
    }

    /**
     * @covers ::getResult
     * @depends testPostResult
     */
    public function testGetResult()
    {
        self::$client->request(Request::METHOD_GET, ResultController::RESULT_API_PATH . '/' . (-1), array(), array(),
            array('HTTP_token' => self::$token));
        self::assertEquals(404, self::$client->getResponse()->getStatusCode());

        self::$client->request(Request::METHOD_POST, ResultController::RESULT_API_PATH, array(), array(),
            array('CONTENT_TYPE' => 'application/json', 'HTTP_token' => self::$token),
            '{"result":9,"user_id":' . self::$createdUserId . '}');
        self::$client->request(Request::METHOD_GET, ResultController::RESULT_API_PATH . '/' . self::$createdResultId, array(), array(),
            array('HTTP_token' => self::$token));
        self::assertEquals(200, self::$client->getResponse()->getStatusCode());
        $body = json_decode(self::$client->getResponse()->getContent());
        self::assertObjectHasAttribute('result', $body);
        self::assertEquals(self::$createdResultId, $body->result->id);
        self::assertEquals(7, $body->result->result);
    }

    /**
     * @covers ::getResultForUser
     * @depends testGetResult
     */
    public function testGetResultForUser()
    {
        self::$client->request(Request::METHOD_POST, ResultController::RESULT_API_PATH, array(), array(),
            array('CONTENT_TYPE' => 'application/json', 'HTTP_token' => self::$token),
            '{"result":9,"user_id":' . self::$createdUserId . '}');
        $recentResult = json_decode(self::$client->getResponse()->getContent())->result->id;

        self::$client->request(Request::METHOD_GET, ResultController::RESULT_API_PATH . '/user/' . (-1), array(), array(),
            array('HTTP_token' => self::$token));
        self::assertEquals(404, self::$client->getResponse()->getStatusCode());

        self::$client->request(Request::METHOD_GET, ResultController::RESULT_API_PATH . '/user/' . self::$createdUserId, array(), array(),
            array('HTTP_token' => self::$token));
        self::assertEquals(200, self::$client->getResponse()->getStatusCode());
        $body = json_decode(self::$client->getResponse()->getContent());
        self::assertObjectHasAttribute('results', $body);
        self::assertEquals($recentResult, $body->results[2]->id);
        self::assertEquals(9, $body->results[2]->result);
    }

    /**
     * @covers ::putResult
     * @depends testGetResultForUser
     */
    public function testPutResult()
    {
        self::$client->request(Request::METHOD_PUT, ResultController::RESULT_API_PATH . '/' . (-1), array(), array(),
            array('CONTENT_TYPE' => 'application/json', 'HTTP_token' => self::$token),
            '{"username":"test2","email":"test2@test2.com","password":"test","enabled":false,"isAdmin":false}');
        self::assertEquals(404, self::$client->getResponse()->getStatusCode());

        self::$client->request(Request::METHOD_PUT, ResultController::RESULT_API_PATH . '/' . self::$createdResultId, array(), array(),
            array('CONTENT_TYPE' => 'application/json', 'HTTP_token' => self::$token),
            '{}');
        self::assertEquals(422, self::$client->getResponse()->getStatusCode());

        self::$client->request(Request::METHOD_PUT, ResultController::RESULT_API_PATH . '/' . self::$createdResultId, array(), array(),
            array('CONTENT_TYPE' => 'application/json', 'HTTP_token' => self::$token),
            '{"result":4}');
        self::assertEquals(209, self::$client->getResponse()->getStatusCode());
        $body = json_decode(self::$client->getResponse()->getContent());
        self::assertEquals(4,$body->result->result);
    }

    /**
     * @covers ::deleteResult
     * @depends testPutResult
     */
    public function testDeleteResult()
    {
        self::$client->request(Request::METHOD_DELETE, ResultController::RESULT_API_PATH . '/' . (-1), array(), array(),
            array('HTTP_token' => self::$token));
        self::assertEquals(404, self::$client->getResponse()->getStatusCode());

        self::$client->request(Request::METHOD_DELETE, ResultController::RESULT_API_PATH . '/' . self::$createdResultId, array(), array(),
            array('HTTP_token' => self::$token));
        self::assertEquals(204, self::$client->getResponse()->getStatusCode());

        self::$client->request(Request::METHOD_GET, ResultController::RESULT_API_PATH . '/' . self::$createdResultId, array(), array(),
            array('HTTP_token' => self::$token));
        self::assertEquals(404, self::$client->getResponse()->getStatusCode());

        self::$client->request(Request::METHOD_DELETE, UserController::USER_API_PATH . '/' . self::$createdUserId, array(), array(),
            array('HTTP_token' => self::$token));
    }

    /**
     * @covers ::authorization
     */
    public function testAuthorization()
    {
        self::$client->request(Request::METHOD_GET, ResultController::RESULT_API_PATH);
        $body = self::$client->getResponse()->getContent();
        self::$client->getResponse()->getStatusCode();
        self::assertEquals(401, self::$client->getResponse()->getStatusCode());
        self::assertJson($body);
        $data = json_decode($body);
        self::assertObjectHasAttribute('message', $data);
        self::assertEquals('Unauthorized invalid token header', $data->message);

        self::$client->request(Request::METHOD_GET, ResultController::RESULT_API_PATH, array(), array(), array(
            'HTTP_token' => self::$notAdminToken
        ));
        self::assertEquals(403, self::$client->getResponse()->getStatusCode());
        self::assertObjectHasAttribute('message', json_decode(self::$client->getResponse()->getContent()));
        self::assertEquals('Forbidden You don\'t have permission to access',
            json_decode(self::$client->getResponse()->getContent())->message);

        self::$client->request(Request::METHOD_GET, ResultController::RESULT_API_PATH, array(), array(), array(
            'HTTP_token' => self::$token
        ));
        self::assertEquals(200, self::$client->getResponse()->getStatusCode());
    }
}