<?php
/**
 * Created by PhpStorm.
 * User: Bruno
 * Date: 14/12/2018
 * Time: 18:38
 */

namespace App\Controller;

use App\Entity\Result;
use App\Entity\User;
use App\Errors;
use App\JWTManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;


/**
 * Class ResultController
 * @package App\Controller
 * @Route(path=ResultController::RESULT_API_PATH, name="api_results_")
 */
class ResultController extends AbstractController
{
    const RESULT_API_PATH = '/api/v1/results';

    /**
     * @param Request $request
     * @return JsonResponse
     * @Route(path="",name="getc",methods={Request::METHOD_GET})
     */
    public function getCResult(Request $request): JsonResponse
    {
        $authoritation = $this->authorization($request);
        if ($authoritation !== true) {
            return $authoritation;
        }
        /** @var Errors $error */
        $error = new Errors();
        $em = $this->getDoctrine()->getManager();
        $results = $em->getRepository(Result::class)->findAll();
        return ($results !== null)
            ? new JsonResponse(['results' => $results])
            : $error->error404();
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @throws \Exception
     * @Route(path="",name="post",methods={Request::METHOD_POST})
     */
    public function postResult(Request $request): JsonResponse
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
        if (!array_key_exists('result', $datos) || !array_key_exists('user_id', $datos)) {
            return $error->error422();
        }
        /** @var User $user */
        $user = $em->getRepository(User::class)->find($datos['user_id']);
        if ($user === null) {
            return $error->error404();
        }
        $result = new Result(
            $datos['result'],
            $user,
            new \DateTime('now')
        );
        $em->persist($result);
        $em->flush();
        return new JsonResponse(['result' => $result], Response::HTTP_CREATED);
    }

    /**
     * @return JsonResponse
     * @Route(path="",name="options",methods={Request::METHOD_OPTIONS})
     */
    public function optionsResult(): JsonResponse
    {
        return new JsonResponse(
            'Allow header',
            Response::HTTP_OK,
            ['allow' => Request::METHOD_GET . ', ' . Request::METHOD_POST]
        );
    }

    /**
     * @param int $id
     * @param Request $request
     * @return JsonResponse
     * @Route(path="/user/{id}",name="get_user",methods={Request::METHOD_GET})
     */
    public function getResultForUser(int $id, Request $request): JsonResponse
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
        $results = $em->getRepository(Result::class)->findBy(['user' => $user]);
        return ($results !== null)
            ? new JsonResponse(['results' => $results])
            : $error->error404();
    }

    /**
     * @param int $id
     * @param Request $request
     * @return JsonResponse
     * @Route(path="/{id}",name="get",methods={Request::METHOD_GET})
     */
    public function getResult(int $id, Request $request): JsonResponse
    {
        $authoritation = $this->authorization($request);
        if ($authoritation !== true) {
            return $authoritation;
        }
        /** @var Errors $error */
        $error = new Errors();
        $em = $this->getDoctrine()->getManager();
        $result = $em->getRepository(Result::class)->find($id);
        return ($result !== null)
            ? new JsonResponse(['result' => $result])
            : $error->error404();
    }

    /**
     * @param int $id
     * @param Request $request
     * @return JsonResponse
     * @Route(path="/{id}",name="put",methods={Request::METHOD_PUT})
     * @throws \Exception
     */
    public function putResult(int $id, Request $request): JsonResponse
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
        /** @var Result $result */
        $result = $em->getRepository(Result::class)->find($id);
        if ($result === null) {
            return $error->error404();
        }
        if (!isset($datos['result'])) {
            return $error->error422();
        }
        $result->setResult($datos['result']);
        $result->setTime(new \DateTime('now'));
        $em->persist($result);
        $em->flush();
        return new JsonResponse(['result' => $result], 209);
    }

    /**
     * @param int $id
     * @param Request $request
     * @return JsonResponse
     * @Route(path="/{id}",name="delete",methods={Request::METHOD_DELETE})
     */
    public function deleteResult(int $id, Request $request): JsonResponse
    {
        $authoritation = $this->authorization($request);
        if ($authoritation !== true) {
            return $authoritation;
        }
        /** @var Errors $error */
        $error = new Errors();
        $em = $this->getDoctrine()->getManager();
        $result = $em->getRepository(Result::class)->find($id);
        if ($result === null) {
            return $error->error404();
        }
        $em->remove($result);
        $em->flush();
        return new JsonResponse(
            null,
            Response::HTTP_NO_CONTENT
        );
    }

    /**
     * @return JsonResponse
     * @Route(path="/{id}",name="options_id",methods={Request::METHOD_OPTIONS})
     */
    public function optionsOneResult(): JsonResponse
    {
        return new JsonResponse(
            'Allow header',
            Response::HTTP_OK,
            ['allow' => Request::METHOD_GET . ', ' . Request::METHOD_PUT . ', ' . Request::METHOD_DELETE]
        );
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