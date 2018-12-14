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
     * @return JsonResponse
     * @Route(path="",name="getc",methods={Request::METHOD_GET})
     */
    public function getCUser(): JsonResponse
    {
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
     * @return JsonResponse
     */
    public function getOneUser(int $id): JsonResponse
    {
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
    public function postUser(Request $request):JsonResponse
    {
        /** @var Errors $error */
        $error = new Errors();
        $em = $this->getDoctrine()->getManager();
        $datosPeticion = $request->getContent();
        $datos = json_decode($datosPeticion, true);
        if (!array_key_exists('username', $datos)||!array_key_exists('email',$datos)||!array_key_exists('password',$datos)) {
            return $error->error422();
        }
        if ($em->getRepository(User::class)->findBy(['username'=>$datos['username']])||
        $em->getRepository(User::class)->findBy(['email'=>$datos['email']])){
            return $error->error400();
        }
        $user = new User(
            $datos['username'],
            $datos['email'],
            $datos['password'],
            $datos['enabled']??true,
            $datos['isAdmin']??false
        );
        $em->persist($user);
        $em->flush();
        return new JsonResponse(['user'=>$user],Response::HTTP_CREATED);
    }

    /**
     * @Route(path="",name="options",methods={Request::METHOD_OPTIONS})
     * @return JsonResponse
     */
    public function optionsUser():JsonResponse
    {
        return new JsonResponse('Allow header',
            Response::HTTP_OK,
            ['allow'=>Request::METHOD_GET.', '.Request::METHOD_POST]
        );
    }

    /**
     * @Route(path="/{id}",name="options_id",methods={Request::METHOD_OPTIONS})
     * @param int id
     * @return JsonResponse
     */
    public function optionsOneUser(int $id):JsonResponse
    {
        return new JsonResponse('Allow header',
            Response::HTTP_OK,
            ['allow'=>Request::METHOD_GET.', '.Request::METHOD_PUT.', '.Request::METHOD_DELETE]
        );
    }
}