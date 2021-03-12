<?php

namespace Integrated\Bundle\WebsiteBundle\Controller;

use Exception;
use Integrated\Bundle\ThemeBundle\Exception\CircularFallbackException;
use Integrated\Bundle\ThemeBundle\Templating\ThemeManager;
use Integrated\Bundle\WebsiteBundle\Security\UserManager;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Bundle\TwigBundle\TwigEngine;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends Controller
{
    /**
     * @var TwigEngine
     */
    private $templating;

    /**
     * @var ThemeManager
     */
    private $themeManager;

    /**
     * @var AuthenticationUtils
     */
    private $authenticationUtils;

    /**
     * @var UserManager
     */
    private $userManager;

    /**
     * @param TwigEngine          $templating
     * @param ThemeManager        $themeManager
     * @param AuthenticationUtils $authenticationUtils
     * @param UserManager         $userManager
     */
    public function __construct(TwigEngine $templating, ThemeManager $themeManager, AuthenticationUtils $authenticationUtils, UserManager $userManager)
    {
        $this->templating = $templating;
        $this->themeManager = $themeManager;
        $this->authenticationUtils = $authenticationUtils;
        $this->userManager = $userManager;
    }

    /**
     * @param Request $request
     *
     * @return Response
     *
     * @throws CircularFallbackException
     */
    public function loginAction(Request $request): Response
    {
        $session = new Session();

        if ($returnUrl = $request->get('returnUrl')) {
            $session->set('returnUrl', $returnUrl);
        }

        if ($this->getUser()) {
            $returnUrl = $session->get('returnUrl', '/');

            return $this->redirect($returnUrl);
        }

        return $this->render(
            $this->themeManager->locateTemplate('security/login.html.twig'),
            [
                'loginEnabled' => $this->userManager->isLoginEnabled(),
                'registrationEnabled' => $this->userManager->isRegistrationEnabled(),
                'lastUsername' => $this->authenticationUtils->getLastUsername(),
                'error' => $this->authenticationUtils->getLastAuthenticationError(),
            ]
        );
    }

    /**
     * @param Request $request
     *
     * @return Response
     */
    public function verifyUsernameAction(Request $request): Response
    {
        $status = $this->userManager->getUsernameStatus($request->request->get('_username'));

        switch ($status) {
            case UserManager::STATUS_USERNAME_NEW:
                $result = ['status' => 'NEW'];
                break;
            case UserManager::STATUS_USERNAME_EXISTS:
                $result = ['status' => 'EXISTS'];
                break;
            case UserManager::STATUS_USERNAME_INVALID:
                $result = ['status' => 'INVALID', 'errorMessage' => 'Please enter a valid e-mail address'];
                break;
            default:
                $result = ['status' => 'ERROR', 'errorMessage' => 'Unkown status'];
                break;
        }

        return new JsonResponse($result);
    }

    /**
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function registerAction(Request $request): JsonResponse
    {
        if ($this->getUser()) {
            return new JsonResponse(['status' => 'ERROR', 'errorMessage' => 'You are already logged in']);
        }

        if (\strlen($request->request->get('password', '')) < 8) {
            return new JsonResponse(['status' => 'ERROR', 'errorMessage' => 'Please choose a password of at least 8 characters']);
        }

        if ($request->request->get('password', '') !== $request->request->get('password-verify', '')) {
            return new JsonResponse(['status' => 'ERROR', 'errorMessage' => 'The two passwords are not the same']);
        }

        try {
            $this->userManager->register($request->request->get('username'), $request->request->get('password'));
        } catch (Exception $exception) {
            return new JsonResponse(['status' => 'ERROR', 'errorMessage' => 'User could not be created']);
        }

        return new JsonResponse(['status' => 'SUCCESS']);
    }
}
