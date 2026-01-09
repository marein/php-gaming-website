<?php

declare(strict_types=1);

namespace Gaming\WebInterface\Presentation\Http;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

final class PageController extends AbstractController
{
    public function homeAction(): Response
    {
        return $this->render('@web-interface/home.html.twig');
    }
}
