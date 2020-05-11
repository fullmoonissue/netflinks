<?php

declare(strict_types=1);

namespace App\Infrastructure\Pdf;

use App\Domain\Entity\Image;
use App\Domain\Entity\Link;
use App\Domain\Entity\Newsletter;
use App\Domain\Link\LinksExtractor;
use App\Domain\Path\PublicImagePathGenerator;
use Exception;
use IntlDateFormatter;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Contracts\Translation\TranslatorInterface;
use TCPDF;

class Netflinks extends TCPDF
{
    public const int HEADER_FOOTER_HEIGHT = 25;
    public const int HEADER_FOOTER_FONT_SIZE = 25;

    public const int HEADER_SPACE_TOP = 7;
    public const int FOOTER_SPACE_BOTTOM = 18;

    public const int LINK_BY_PAGE_COUNT = 20;

    private Newsletter $newsletter;

    public function __construct(
        private readonly PublicImagePathGenerator $publicImagesPathGenerator,
        private readonly LinksExtractor $linksExtractor,
        private readonly TranslatorInterface $translator,
    ) {
        parent::__construct();
    }

    public function Header(): void
    {
        $this->cleanPage();

        // Header text
        $this->SetFont('times', 'BI', self::HEADER_FOOTER_FONT_SIZE);
        $text = $this->translator->trans('newsletter.header');
        $this->Text($this->centerText($text, self::HEADER_FOOTER_FONT_SIZE), self::HEADER_SPACE_TOP, $text);

        // Delimiter
        $this->Rect(0, self::HEADER_FOOTER_HEIGHT, $this->getPageWidth(), 0.2);
    }

    public function Footer(): void
    {
        // Delimiter
        $this->Rect(0, $this->getPageHeight() - self::HEADER_FOOTER_HEIGHT, $this->getPageWidth(), 0.2);

        // Footer text
        $this->SetFont('times', 'BI', self::HEADER_FOOTER_FONT_SIZE);
        $text = $this->translator->trans(
            'newsletter.footer',
            [
                '%date%' => (new IntlDateFormatter(
                    $this->translator->getLocale(),
                    IntlDateFormatter::FULL,
                    IntlDateFormatter::NONE,
                ))->format($this->newsletter->getDate()),
            ]
        );
        $this->Text(
            $this->centerText($text, self::HEADER_FOOTER_FONT_SIZE),
            $this->getPageHeight() - self::FOOTER_SPACE_BOTTOM,
            $text
        );
    }

    public function prepareDocument(Newsletter $newsletter): void
    {
        $this->newsletter = $newsletter;
        $this->SetAutoPageBreak(true, 1);

        $x = 20;
        $ySpace = 10;
        $this->setTextColor(0, 0, 0);

        $links = $this->linksExtractor->extract($this->newsletter);

        $images = $this->newsletter->getImages();

        /** @var string[] $imageAbsoluteFilePaths */
        $imageAbsoluteFilePaths = array_map(
            function (Image $image) {
                return $this->publicImagesPathGenerator->generateAbsoluteFilePath($image);
            },
            $images->toArray()
        );

        $linksByCategory = [];
        foreach ($links as $link) {
            if (!isset($linksByCategory[$link->getCategory()->getName()])) {
                $linksByCategory[$link->getCategory()->getName()] = [];
            }
            $linksByCategory[$link->getCategory()->getName()][] = $link;
        }

        if (count(array_keys($linksByCategory)) !== count($imageAbsoluteFilePaths)) {
            throw new Exception(
                $this->translator->trans(
                    'error.images_needed',
                    [
                        '%imageCountNeeded%' => count(array_keys($linksByCategory)),
                        '%imageCountPrepared%' => count($imageAbsoluteFilePaths),
                    ]
                )
            );
        }

        foreach ($images as $image) {
            if (!$image->getRecipients()->contains($newsletter->getRecipient())) {
                throw new Exception(
                    $this->translator->trans('error.images_not_all_assigned')
                );
            }
        }

        ksort($linksByCategory);
        sort($imageAbsoluteFilePaths);

        $indexImagePath = -1;
        foreach ($linksByCategory as $category => $links) {
            $this->AddPage();
            $this->autoCenterImage(
                $imageAbsoluteFilePaths[++$indexImagePath],
                self::HEADER_FOOTER_HEIGHT,
                self::HEADER_FOOTER_HEIGHT + 10
            );

            usort(
                $links,
                fn (Link $left, Link $right): int => strtolower($this->removeAccent($left->getDescription()))
                    <=>
                    strtolower($this->removeAccent($right->getDescription()))
            );

            $chunks = array_chunk($links, self::LINK_BY_PAGE_COUNT);
            foreach ($chunks as $chunkLinks) {
                $this->AddPage();

                $fontSize = 25;
                $this->SetFont('dejavusans', 'BI', $fontSize);
                $richCategory = sprintf('--> %s', $category);
                $this->Text($x, 30, $richCategory);
                $this->Rect(0, 45, $this->getPageWidth(), 0.2);

                $y = 50;
                /** @var Link $link */
                foreach ($chunkLinks as $link) {
                    $y += $ySpace;
                    $this->SetFont('dejavusans', 'U', 13);
                    $this->Text($x, $y, $link->getDescription(), link: $link->getUrl());
                }
            }
        }
    }

    private function removeAccent(string $value): string
    {
        // cf https://stackoverflow.com/a/34649673
        $map = [
            'à' => 'a',
            'á' => 'a',
            'â' => 'a',
            'ã' => 'a',
            'ä' => 'a',
            'ą' => 'a',
            'å' => 'a',
            'ā' => 'a',
            'ă' => 'a',
            'ǎ' => 'a',
            'ǻ' => 'a',
            'À' => 'A',
            'Á' => 'A',
            'Â' => 'A',
            'Ã' => 'A',
            'Ä' => 'A',
            'Ą' => 'A',
            'Å' => 'A',
            'Ā' => 'A',
            'Ă' => 'A',
            'Ǎ' => 'A',
            'Ǻ' => 'A',

            'ç' => 'c',
            'ć' => 'c',
            'ĉ' => 'c',
            'ċ' => 'c',
            'č' => 'c',
            'Ç' => 'C',
            'Ć' => 'C',
            'Ĉ' => 'C',
            'Ċ' => 'C',
            'Č' => 'C',

            'ď' => 'd',
            'đ' => 'd',
            'Ð' => 'D',
            'Ď' => 'D',
            'Đ' => 'D',

            'è' => 'e',
            'é' => 'e',
            'ê' => 'e',
            'ë' => 'e',
            'ę' => 'e',
            'ē' => 'e',
            'ĕ' => 'e',
            'ė' => 'e',
            'ě' => 'e',
            'È' => 'E',
            'É' => 'E',
            'Ê' => 'E',
            'Ë' => 'E',
            'Ę' => 'E',
            'Ē' => 'E',
            'Ĕ' => 'E',
            'Ė' => 'E',
            'Ě' => 'E',

            'ƒ' => 'f',

            'ĝ' => 'g',
            'ğ' => 'g',
            'ġ' => 'g',
            'ģ' => 'g',
            'Ĝ' => 'G',
            'Ğ' => 'G',
            'Ġ' => 'G',
            'Ģ' => 'G',

            'ĥ' => 'h',
            'ħ' => 'h',
            'Ĥ' => 'H',
            'Ħ' => 'H',

            'ì' => 'i',
            'í' => 'i',
            'î' => 'i',
            'ï' => 'i',
            'ĩ' => 'i',
            'ī' => 'i',
            'ĭ' => 'i',
            'į' => 'i',
            'ſ' => 'i',
            'ǐ' => 'i',
            'Ì' => 'I',
            'Í' => 'I',
            'Î' => 'I',
            'Ï' => 'I',
            'Ĩ' => 'I',
            'Ī' => 'I',
            'Ĭ' => 'I',
            'Į' => 'I',
            'İ' => 'I',
            'Ǐ' => 'I',

            'ĵ' => 'j',
            'Ĵ' => 'J',

            'ķ' => 'k',
            'Ķ' => 'K',

            'ł' => 'l',
            'ĺ' => 'l',
            'ļ' => 'l',
            'ľ' => 'l',
            'ŀ' => 'l',
            'Ł' => 'L',
            'Ĺ' => 'L',
            'Ļ' => 'L',
            'Ľ' => 'L',
            'Ŀ' => 'L',

            'ñ' => 'n',
            'ń' => 'n',
            'ņ' => 'n',
            'ň' => 'n',
            'ŉ' => 'n',
            'Ñ' => 'N',
            'Ń' => 'N',
            'Ņ' => 'N',
            'Ň' => 'N',

            'ò' => 'o',
            'ó' => 'o',
            'ô' => 'o',
            'õ' => 'o',
            'ö' => 'o',
            'ð' => 'o',
            'ø' => 'o',
            'ō' => 'o',
            'ŏ' => 'o',
            'ő' => 'o',
            'ơ' => 'o',
            'ǒ' => 'o',
            'ǿ' => 'o',
            'Ò' => 'O',
            'Ó' => 'O',
            'Ô' => 'O',
            'Õ' => 'O',
            'Ö' => 'O',
            'Ø' => 'O',
            'Ō' => 'O',
            'Ŏ' => 'O',
            'Ő' => 'O',
            'Ơ' => 'O',
            'Ǒ' => 'O',
            'Ǿ' => 'O',

            'ŕ' => 'r',
            'ŗ' => 'r',
            'ř' => 'r',
            'Ŕ' => 'R',
            'Ŗ' => 'R',
            'Ř' => 'R',

            'ś' => 's',
            'š' => 's',
            'ŝ' => 's',
            'ş' => 's',
            'Ś' => 'S',
            'Š' => 'S',
            'Ŝ' => 'S',
            'Ş' => 'S',

            'ţ' => 't',
            'ť' => 't',
            'ŧ' => 't',
            'Ţ' => 'T',
            'Ť' => 'T',
            'Ŧ' => 'T',

            'ù' => 'u',
            'ú' => 'u',
            'û' => 'u',
            'ü' => 'u',
            'ũ' => 'u',
            'ū' => 'u',
            'ŭ' => 'u',
            'ů' => 'u',
            'ű' => 'u',
            'ų' => 'u',
            'ư' => 'u',
            'ǔ' => 'u',
            'ǖ' => 'u',
            'ǘ' => 'u',
            'ǚ' => 'u',
            'ǜ' => 'u',
            'Ù' => 'U',
            'Ú' => 'U',
            'Û' => 'U',
            'Ü' => 'U',
            'Ũ' => 'U',
            'Ū' => 'U',
            'Ŭ' => 'U',
            'Ů' => 'U',
            'Ű' => 'U',
            'Ų' => 'U',
            'Ư' => 'U',
            'Ǔ' => 'U',
            'Ǖ' => 'U',
            'Ǘ' => 'U',
            'Ǚ' => 'U',
            'Ǜ' => 'U',

            'ŵ' => 'w',
            'Ŵ' => 'W',

            'ý' => 'y',
            'ÿ' => 'y',
            'ŷ' => 'y',
            'Ý' => 'Y',
            'Ÿ' => 'Y',
            'Ŷ' => 'Y',

            'ż' => 'z',
            'ź' => 'z',
            'ž' => 'z',
            'Ż' => 'Z',
            'Ź' => 'Z',
            'Ž' => 'Z',

            'Ǽ' => 'A',
            'ǽ' => 'a',
        ];

        return strtr($value, $map);
    }

    protected function cleanPage(): void
    {
        $this->Rect(0, 0, $this->getPageWidth(), $this->getPageHeight(), 'F', [], [255, 255, 255]);
    }

    public function downloadAsString(): string
    {
        return $this->Output('', 'S');
    }

    protected function centerText(string $text, int $fontSize): float
    {
        return floor(($this->getPageWidth() - strlen($text) * $fontSize / 25 * 3.7) / 2);
    }

    protected function autoCenterImage(string $imagePath, int $headerHeight, int $footerHeight): void
    {
        if (!(new Filesystem())->exists($imagePath)) {
            return;
        }

        $scaleDivisor = 1;
        $i1 = getimagesize($imagePath);
        do {
            $w1 = floor($i1[0] / $scaleDivisor);
            $h1 = floor($i1[1] / $scaleDivisor);
            $x1 = floor(($this->getPageWidth() - $w1) / 2);
            $y1 = $headerHeight + floor(($this->getPageHeight() - $h1 - 2 * $footerHeight) / 2);

            $scaleDivisorFound = ($w1 < $this->getPageWidth() && $h1 < $this->getPageHeight() - 2 * $footerHeight);
            ++$scaleDivisor;
        } while (!$scaleDivisorFound);

        $this->Image($imagePath, $x1, $y1, $w1, $h1);
    }
}
