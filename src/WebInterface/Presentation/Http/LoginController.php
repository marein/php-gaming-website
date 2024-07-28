<?php

declare(strict_types=1);

namespace Gaming\WebInterface\Presentation\Http;

use Gaming\Common\Bus\Bus;
use Gaming\Common\Bus\Exception\ApplicationException;
use Gaming\Common\Bus\Integration\FormViolationMapper;
use Gaming\Identity\Application\User\Query\UserByEmailQuery;
use Gaming\WebInterface\Infrastructure\Security\Security;
use Gaming\WebInterface\Infrastructure\Security\User;
use Gaming\WebInterface\Presentation\Http\Form\LoginType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Security\Http\LoginLink\LoginLinkHandlerInterface;

final class LoginController extends AbstractController
{
    public function __construct(
        private readonly Security $security,
        private readonly AuthenticationUtils $authenticationUtils,
        private readonly LoginLinkHandlerInterface $loginLinkHandler,
        private readonly Bus $identityQueryBus,
        private readonly FormViolationMapper $formViolationMapper
    ) {
    }

    public function indexAction(Request $request): Response
    {
        if ($this->security->getUser()->isSignedUp) {
            return $this->redirectToRoute('lobby');
        }

        $form = $this->createForm(LoginType::class)
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $user = $this->identityQueryBus->handle(
                    new UserByEmailQuery((string)$form->get('email')->getData())
                );

                return $this->redirectToRoute('login_check_inbox', [
                    'loginUrl' => $user === null ? null : $this->loginLinkHandler->createLoginLink(
                        new User($user->userId)
                    )
                ]);
            } catch (ApplicationException $e) {
                $this->formViolationMapper->mapViolations($form, $e->violations());
            }
        }

        return $this->render('@web-interface/login/index.html.twig', [
            'form' => $form,
            'lastAuthenticationError' => $this->authenticationUtils->getLastAuthenticationError()
        ]);
    }

    public function checkInboxAction(): Response
    {
        if ($this->security->getUser()->isSignedUp) {
            return $this->redirectToRoute('lobby');
        }

        return $this->render('@web-interface/login/check-inbox.html.twig');
    }
}
