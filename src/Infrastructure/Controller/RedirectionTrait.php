<?php

declare(strict_types=1);

namespace App\Infrastructure\Controller;

use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

trait RedirectionTrait
{
    protected function redirectToIndex(): RedirectResponse
    {
        if (!empty($this->adminUrlGenerator)) {
            return new RedirectResponse(
                $this
                    ->adminUrlGenerator
                    ->setController(self::class)
                    ->setAction(Action::INDEX)
                    ->generateUrl()
            );
        }

        throw new NotFoundHttpException();
    }
}
