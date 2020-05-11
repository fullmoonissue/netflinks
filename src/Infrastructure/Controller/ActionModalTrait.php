<?php

declare(strict_types=1);

namespace App\Infrastructure\Controller;

use EasyCorp\Bundle\EasyAdminBundle\Config\Action;

trait ActionModalTrait
{
    protected function actionAsModal(Action $action, string $text): void
    {
        $action
            ->setHtmlAttributes(
                [
                    'onclick' => sprintf(
                        'alert("%s"); return false;',
                        str_replace(
                            '"',
                            '\"',
                            $text
                        )
                    ),
                ]
            )
            ->linkToCrudAction('useless_here');
    }
}
