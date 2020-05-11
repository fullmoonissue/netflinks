<?php

declare(strict_types=1);

namespace App\Domain\Path;

use App\Domain\Entity\Newsletter;

class PublicPdfPathGenerator
{
    private const string PUBLIC_PDF_RELATIVE_FOLDER_PATH = '/assets/pdfs';

    public function __construct(
        private readonly PublicPathGenerator $publicPathGenerator,
    ) {
    }

    public function generateAbsoluteFilePath(Newsletter $newsletter): string
    {
        return sprintf(
            '%s/%s/%s-%s.pdf',
            $this->publicPathGenerator->generateAbsoluteFolderPath(),
            ltrim(self::PUBLIC_PDF_RELATIVE_FOLDER_PATH, '/'),
            $newsletter->getRecipient()->getShort(),
            $newsletter->getDate()->format('Y-m-d')
        );
    }
}
