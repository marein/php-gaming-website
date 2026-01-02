<?php

declare(strict_types=1);

namespace Gaming\Identity\Port\Adapter\Http;

use Gaming\Common\Bus\Bus;
use Gaming\Common\Domain\Exception\DomainException;
use Gaming\Common\Domain\Integration\FormViolationMapper;
use Gaming\Identity\Application\User\Command\SignUpCommand;
use Gaming\Identity\Port\Adapter\Http\Form\SignupType;
use Gaming\WebInterface\Infrastructure\Security\Security;
use Gaming\WebInterface\Infrastructure\Security\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\UriSigner;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

final class SignupController extends AbstractController
{
    public function __construct(
        private readonly UriSigner $uriSigner,
        private readonly Security $security,
        private readonly Bus $identityCommandBus,
        private readonly FormViolationMapper $formViolationMapper
    ) {
    }

    public function indexAction(#[CurrentUser] ?User $user, Request $request): Response
    {
        if ($user?->isSignedUp) {
            return $this->redirectToRoute('home');
        }

        $form = $this->createForm(SignupType::class, $request->query->all())
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $this->identityCommandBus->handle(
                    new SignUpCommand(
                        $this->security->forceUser()->getUserIdentifier(),
                        (string)$form->get('email')->getData(),
                        (string)$form->get('username')->getData(),
                        true
                    )
                );

                return $this->redirectToRoute('identity_signup_verify_email', [
                    'username' => $form->get('username')->getData(),
                    'confirmUrl' => $this->uriSigner->sign(
                        $this->generateUrl(
                            'identity_signup_confirm',
                            $form->getData(),
                            UrlGeneratorInterface::ABSOLUTE_URL
                        )
                    )
                ]);
            } catch (DomainException $e) {
                $this->formViolationMapper->mapViolations($form, $e->violations);
            }
        }

        return $this->render('@identity/signup/index.html.twig', ['form' => $form]);
    }

    public function verifyEmailAction(#[CurrentUser] ?User $user, Request $request): Response
    {
        if ($user?->isSignedUp) {
            return $this->redirectToRoute('home');
        }

        return $this->render('@identity/signup/verify-email.html.twig');
    }

    public function confirmAction(#[CurrentUser] ?User $user, Request $request): Response
    {
        if ($user?->isSignedUp) {
            return $this->redirectToRoute('home');
        }

        if (!$this->uriSigner->checkRequest($request)) {
            $this->addFlash(
                'danger',
                'Oops! Your verification link is either invalid or has expired.
                Please sign up again to receive a new link.'
            );

            return $this->redirectToRoute('signup');
        }

        $form = $this->createForm(SignupType::class, $request->query->all(), ['confirm' => true])
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $this->identityCommandBus->handle(
                    new SignUpCommand(
                        $this->security->forceUser()->getUserIdentifier(),
                        (string)$form->get('email')->getData(),
                        (string)$form->get('username')->getData(),
                        false
                    )
                );

                $this->security->forceUser()->forceRefreshAtNextRequest();

                return $this->redirectToRoute('home');
            } catch (DomainException $e) {
                $this->formViolationMapper->mapViolations($form, $e->violations);
            }
        }

        return $this->render('@identity/signup/confirm.html.twig', ['form' => $form]);
    }
}
