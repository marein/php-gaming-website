<?php

declare(strict_types=1);

namespace Gaming\WebInterface\Presentation\Http;

use Gaming\Common\Bus\Bus;
use Gaming\Common\Bus\Exception\ApplicationException;
use Gaming\Common\Bus\Integration\FormViolationMapper;
use Gaming\Identity\Application\User\Command\SignUpCommand;
use Gaming\WebInterface\Infrastructure\Security\Security;
use Gaming\WebInterface\Infrastructure\Security\User;
use Gaming\WebInterface\Presentation\Http\Form\SignupType;
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
            return $this->redirectToRoute('lobby');
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

                return $this->redirectToRoute('signup_verify_email', [
                    'username' => $form->get('username')->getData(),
                    'confirmUrl' => $this->uriSigner->sign(
                        $this->generateUrl('signup_confirm', $form->getData(), UrlGeneratorInterface::ABSOLUTE_URL)
                    )
                ]);
            } catch (ApplicationException $e) {
                $this->formViolationMapper->mapViolations($form, $e->violations());
            }
        }

        return $this->render('@web-interface/signup/index.html.twig', ['form' => $form]);
    }

    public function verifyEmailAction(#[CurrentUser] ?User $user, Request $request): Response
    {
        if ($user?->isSignedUp) {
            return $this->redirectToRoute('lobby');
        }

        return $this->render('@web-interface/signup/verify-email.html.twig');
    }

    public function confirmAction(#[CurrentUser] ?User $user, Request $request): Response
    {
        if ($user?->isSignedUp) {
            return $this->redirectToRoute('lobby');
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

                return $this->redirectToRoute('lobby');
            } catch (ApplicationException $e) {
                $this->formViolationMapper->mapViolations($form, $e->violations());
            }
        }

        return $this->render('@web-interface/signup/confirm.html.twig', ['form' => $form]);
    }
}
