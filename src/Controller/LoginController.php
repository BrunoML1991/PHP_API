<?php
/**
 * Created by PhpStorm.
 * User: Bruno
 * Date: 16/12/2018
 * Time: 20:40
 */

namespace App\Controller;

use App\Entity\User;
use App\Errors;
use App\JWTManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class LoginController
 * @package App\Controller
 * @Route(path=LoginController::LOGIN_API_PATH,name="api_login_")
 */
class LoginController extends AbstractController
{
    const LOGIN_API_PATH = '/api/v1/login_check';

    /**
     * @param Request $request
     * @return JsonResponse
     * @Route(path="",name="post",methods={Request::METHOD_POST})
     */
    public function postLogin(Request $request): JsonResponse
    {
        /** @var Errors $error */
        $error = new Errors();
        $em = $this->getDoctrine()->getManager();
        $datosPeticion = $request->getContent();
        $datos = json_decode($datosPeticion, true);
        if (!array_key_exists('_username', $datos) || !array_key_exists('_password', $datos)) {
            return $error->error401();
        }
        $users = $em->getRepository(User::class)->findBy(['username' => $datos['_username']]);
        if (count($users) === 0) {
            return $error->error401();
        }
        /** @var User $user */
        $user = $users[0];
        if (!$user->validatePassword($datos['_password'])) {
            return $error->error401();
        }
        $jwt = new JWTManager();
        return new JsonResponse(['token' => $jwt->createToken($user)]);
    }
}