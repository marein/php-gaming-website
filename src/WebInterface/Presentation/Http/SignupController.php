<?php

declare(strict_types=1);

namespace Gaming\WebInterface\Presentation\Http;

use Gaming\Common\Bus\Exception\ApplicationException;
use Gaming\Common\Bus\Integration\FormViolationMapper;
use Gaming\WebInterface\Application\IdentityService;
use Gaming\WebInterface\Infrastructure\Security\Security;
use Gaming\WebInterface\Presentation\Http\Form\SignupType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\UriSigner;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class SignupController extends AbstractController
{
    public function __construct(
        private readonly UriSigner $uriSigner,
        private readonly Security $security,
        private readonly IdentityService $identityService,
        private readonly FormViolationMapper $formViolationMapper
    ) {
    }

    public function indexAction(Request $request): Response
    {
        $form = $this->createForm(SignupType::class, $request->query->all())
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $this->identityService->signUp(
                    $this->security->getUser()->getUserIdentifier(),
                    (string)$form->get('email')->getData(),
                    (string)$form->get('username')->getData(),
                    true
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

    public function confirmAction(Request $request): Response
    {
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
                $this->identityService->signUp(
                    $this->security->getUser()->getUserIdentifier(),
                    (string)$form->get('email')->getData(),
                    (string)$form->get('username')->getData()
                );

                $this->security->getUser()->forceRefreshAtNextRequest();

                return $this->redirectToRoute('lobby');
            } catch (ApplicationException $e) {
                $this->formViolationMapper->mapViolations($form, $e->violations());
            }
        }

        return $this->render('@web-interface/signup/confirm.html.twig', ['form' => $form]);
    }
}
