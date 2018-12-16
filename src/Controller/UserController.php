<?php
/**
 * Created by PhpStorm.
 * User: Bruno
 * Date: 14/12/2018
 * Time: 18:38
 */

namespace App\Controller;

use App\Errors;
use App\Entity\User;
use App\JWTManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class UserController
 * @package App\Controller
 * @Route(path=UserController::USER_API_PATH, name="api_users_")
 */
class UserController extends AbstractController
{
    const USER_API_PATH = '/api/v1/users';

    /**
     * @param Request $request
     * @return JsonResponse
     * @Route(path="",name="getc",methods={Request::METHOD_GET})
     */
    public function getCUser(Request $request): JsonResponse
    {
        $authoritation = $this->authorization($request);
        if ($authoritation !== true) {
            return $authoritation;
        }
        /** @var Errors $error */
        $error = new Errors();
        $em = $this->getDoctrine()->getManager();
        $users = $em->getRepository(User::class)->findAll();
        return ($users !== null)
            ? new JsonResponse(['users' => $users])
            : $error->error404();
    }

    /**
     * @Route(path="/{id}",name="get",methods={Request::METHOD_GET})
     * @param int id
     * @param Request $request
     * @return JsonResponse
     */
    public function getOneUser(int $id, Request $request): JsonResponse
    {
        $authoritation = $this->authorization($request);
        if ($authoritation !== true) {
            return $authoritation;
        }
        /** @var Errors $error */
        $error = new Errors();
        $em = $this->getDoctrine()->getManager();
        $user = $em->getRepository(User::class)->find($id);
        return ($user !== null)
            ? new JsonResponse(['user' => $user])
            : $error->error404();
    }

    /**
     * @Route(path="",name="post",methods={Request::METHOD_POST})
     * @param Request $request
     * @return JsonResponse
     */
    public function postUser(Request $request): JsonResponse
    {
        $authoritation = $this->authorization($request);
        if ($authoritation !== true) {
            return $authoritation;
        }
        /** @var Errors $error */
        $error = new Errors();
        $em = $this->getDoctrine()->getManager();
        $datosPeticion = $request->getContent();
        $datos = json_decode($datosPeticion, true);
        if (!array_key_exists('username', $datos) || !array_key_exists('email', $datos) || !array_key_exists('password', $datos)) {
            return $error->error422();
        }
        if ($em->getRepository(User::class)->findBy(['username' => $datos['username']]) ||
            $em->getRepository(User::class)->findBy(['email' => $datos['email']])) {
            return $error->error400();
        }
        $user = new User(
            $datos['username'],
            $datos['email'],
            $datos['password'],
            $datos['enabled'] ?? true,
            $datos['isAdmin'] ?? false
        );
        $em->persist($user);
        $em->flush();
        return new JsonResponse(['user' => $user], Response::HTTP_CREATED);
    }

    /**
     * @Route(path="",name="options",methods={Request::METHOD_OPTIONS})
     * @return JsonResponse
     */
    public function optionsUser(): JsonResponse
    {
        return new JsonResponse(
            'Allow header',
            Response::HTTP_OK,
            ['allow' => Request::METHOD_GET . ', ' . Request::METHOD_POST]
        );
    }

    /**
     * @Route(path="/{id}",name="options_id",methods={Request::METHOD_OPTIONS})
     * @return JsonResponse
     */
    public function optionsOneUser(): JsonResponse
    {
        return new JsonResponse(
            'Allow header',
            Response::HTTP_OK,
            ['allow' => Request::METHOD_GET . ', ' . Request::METHOD_PUT . ', ' . Request::METHOD_DELETE]
        );
    }

    /**
     * @Route(path="/{id}",name="delete",methods={Request::METHOD_DELETE})
     * @param int $id
     * @param Request $request
     * @return JsonResponse
     */
    public function deleteUser(int $id, Request $request): JsonResponse
    {
        $authoritation = $this->authorization($request);
        if ($authoritation !== true) {
            return $authoritation;
        }
        /** @var Errors $error */
        $error = new Errors();
        $em = $this->getDoctrine()->getManager();
        $user = $em->getRepository(User::class)->find($id);
        if ($user === null) {
            return $error->error404();
        }
        $em->remove($user);
        $em->flush();
        return new JsonResponse(
            null,
            Response::HTTP_NO_CONTENT
        );
    }

    /**
     * @Route(path="/{id}",name="put",methods={Request::METHOD_PUT})
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function putUser(Request $request, int $id): JsonResponse
    {
        $authoritation = $this->authorization($request);
        if ($authoritation !== true) {
            return $authoritation;
        }
        /** @var Errors $error */
        $error = new Errors();
        $em = $this->getDoctrine()->getManager();
        $datosPeticion = $request->getContent();
        $datos = json_decode($datosPeticion, true);
        /** @var User $user */
        $user = $em->getRepository(User::class)->find($id);
        if ($user === null) {
            return $error->error404();
        }
        if (isset($datos['username'])) {
            if ($em->getRepository(User::class)->findBy(['username' => $datos['username']])) {
                return $error->error400();
            }
        }
        if (isset($datos['email'])) {
            if ($em->getRepository(User::class)->findBy(['email' => $datos['email']])) {
                return $error->error400();
            }
        }
        if (isset($datos['username'])) {
            $user->setUsername($datos['username']);
        }
        if (isset($datos['email'])) {
            $user->setEmail($datos['email']);
        }
        if (isset($datos['password'])) {
            $user->setPassword($datos['password']);
        }
        if (isset($datos['enabled'])) {
            $user->setEnabled($datos['enabled']);
        }
        if (isset($datos['isAdmin'])) {
            $user->setIsAdmin($datos['isAdmin']);
        }
        $em->persist($user);
        $em->flush();
        return new JsonResponse($user, 209);
    }

    private function authorization(Request $request)
    {
        /** @var Errors $error */
        $error = new Errors();
        $jwtManager = new JWTManager();
        $token = $request->headers->get('token');
        if ($token === null || !$jwtManager->isValidToken($token)) {
            return $error->error401();
        }
        if (!$jwtManager->isAdminUser($token)) {
            return $error->error403();
        }
        return true;
    }
}