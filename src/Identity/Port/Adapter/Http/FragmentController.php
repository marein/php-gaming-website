<?php

declare(strict_types=1);

namespace Gaming\Identity\Port\Adapter\Http;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

final class FragmentController extends AbstractController
{
    public function homeHeaderAction(): Response
    {
        return $this->render('@identity/fragment/home-header.html.twig');
    }
}
