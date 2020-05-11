<?php

declare(strict_types=1);

namespace App\Infrastructure\Controller;

use App\Domain\Entity\Category;
use App\Domain\Entity\Image;
use App\Domain\Entity\Link;
use App\Domain\Entity\Newsletter;
use App\Domain\Entity\Recipient;
use App\Domain\Entity\Tag;
use App\Domain\Image\UnregisteredImageExtractor;
use App\Domain\Path\PublicImagePathGenerator;
use App\Domain\Repository\ImageRepositoryInterface;
use App\Infrastructure\Repository\RecipientRepository;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use League\CommonMark\Exception\CommonMarkException;
use League\CommonMark\Extension\Attributes\AttributesExtension;
use League\CommonMark\GithubFlavoredMarkdownConverter;
use League\CommonMark\Output\RenderedContentInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

class DashboardController extends AbstractDashboardController
{
    private const string PROJECT_NAME = 'Netflinks';

    /**
     * @param string[] $locales
     */
    public function __construct(
        private readonly RecipientRepository $recipientRepository,
        private readonly ImageRepositoryInterface $imageRepository,
        private readonly UnregisteredImageExtractor $unregisteredImageExtractor,
        private readonly TranslatorInterface $translator,
        private readonly PublicImagePathGenerator $publicImagePathGenerator,
        private readonly array $locales,
        private readonly string $projectDirectory,
    ) {
    }

    #[Route('/admin/{_locale}', name: 'admin_i18n')]
    public function index(): Response
    {
        $dashboardContent = file_get_contents(
            sprintf(
                '%s/docs/%s.md',
                $this->projectDirectory,
                $this->translator->getLocale(),
            )
        );

        // EAB translation file : vendor/easycorp/easyadmin-bundle/src/Resources/translations/EasyAdminBundle.{lang}.php
        $dashboardContent = preg_replace_callback(
            '/%%eab.(?P<translation_key>[a-z._]+)%%/',
            function (array $matches) {
                return $this->translator->trans($matches['translation_key'], domain: 'EasyAdminBundle');
            },
            $dashboardContent
        );

        $dashboardContent = preg_replace_callback(
            '/%%(?P<translation_key>[a-z._]+)%%/',
            function (array $matches) {
                return $this->translator->trans($matches['translation_key']);
            },
            $dashboardContent
        );

        $paths = [
            'images.folder.path' => $this->publicImagePathGenerator->generateRelativeFolderPath(),
        ];

        $dashboardContent = preg_replace_callback(
            '/##(?P<path>[a-z._]+)##/',
            function (array $matches) use ($paths) {
                return $paths[$matches['path']];
            },
            $dashboardContent
        );

        try {
            $dashboardContent = $this->convertMarkdownToHtml($dashboardContent);
        } catch (CommonMarkException) {
            $this->addFlash(
                'warning',
                $this->translator->trans('error.markdown_not_convertable_to_html')
            );
        }

        return $this->render(
            'dashboard.html.twig',
            [
                'project_name' => self::PROJECT_NAME,
                'dashboard_content' => $dashboardContent,
            ]
        );
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle(self::PROJECT_NAME)
            ->setLocales($this->locales);
    }

    public function configureMenuItems(): iterable
    {
        yield MenuItem::linkToDashboard('menu.dashboard', 'fa fa-home');

        // --- Section about links --- //

        yield MenuItem::section('menu.links');
        yield MenuItem::linkToCrud('index.links', 'fas fa-link', Link::class);
        yield MenuItem::linkToCrud('index.favorites', 'fas fa-star', Link::class)
            ->setQueryParameter('filters[isFavorite]', 1);
        yield MenuItem::linkToCrud('action.add.link', 'fas fa-plus', Link::class)
            ->setAction(Action::NEW);

        // --- Section about categories --- //

        yield MenuItem::section('menu.category');
        yield MenuItem::linkToCrud('index.categories', 'fas fa-layer-group', Category::class);
        yield MenuItem::linkToCrud('action.add.category', 'fas fa-plus', Category::class)
            ->setAction(Action::NEW);

        // --- Section about recipients --- //

        yield MenuItem::section('menu.recipient');
        yield MenuItem::linkToCrud('index.recipients', 'fas fa-users', Recipient::class);
        yield MenuItem::linkToCrud('action.add.recipient', 'fas fa-plus', Recipient::class)
            ->setAction(Action::NEW);

        // --- Section about tags --- //

        yield MenuItem::section('menu.tag');
        yield MenuItem::linkToCrud('index.tags', 'fas fa-tag', Tag::class);
        yield MenuItem::linkToCrud('action.add.tag', 'fas fa-plus', Tag::class)
            ->setAction(Action::NEW);

        // --- Section about images --- //

        yield MenuItem::section('menu.image');

        yield MenuItem::linkToCrud('index.images', 'fas fa-image', Image::class);

        $imagesNotSaved = $this->unregisteredImageExtractor->extract();
        yield MenuItem::linkToCrud('action.images_not_saved', 'fas fa-floppy-disk', Image::class)
            ->setAction('ingestNewImages')
            ->setBadge($count = count($imagesNotSaved), 0 === $count ? 'success' : 'danger')
            ->setHtmlAttribute(
                'onclick',
                sprintf(
                    'if (0 === %d) { alert("%s"); return false; } else { return confirm("%s"); }',
                    count($imagesNotSaved),
                    str_replace(
                        '"',
                        '\"',
                        $this->translator->trans('alert.nothing_to_ingest')
                    ),
                    str_replace(
                        '"',
                        '\"',
                        $this->translator->trans('alert.confirm_ingest_unsaved_images')
                    )
                ),
            );

        $imageIdsUsedByEveryone = $this->imageRepository->findIdsUsedByEveryone();
        yield MenuItem::linkToCrud('index.images_used_by_everyone', 'fas fa-users-viewfinder', Image::class)
            ->setQueryParameter('used_by_everyone', 1)
            ->setBadge($count = count($imageIdsUsedByEveryone), 0 === $count ? 'success' : 'warning')
            ->setHtmlAttribute(
                'onclick',
                sprintf(
                    'if (0 === %d) { alert("%s"); return false; }',
                    count($imageIdsUsedByEveryone),
                    str_replace(
                        '"',
                        '\"',
                        $this->translator->trans('alert.no_image_used_by_everyone')
                    ),
                ),
            );

        // --- Section about newsletters --- //

        yield MenuItem::section('menu.newsletter');
        yield MenuItem::linkToCrud('index.newsletters', 'fas fa-newspaper', Newsletter::class);

        $recipientsWithoutNL = count($this->recipientRepository
            ->filteredAndOrderedForAssociationField()
            ->getQuery()->getResult());
        if (0 < $recipientsWithoutNL) {
            yield MenuItem::linkToCrud('action.add.newsletter', 'fas fa-plus', Newsletter::class)
                ->setAction(Action::NEW);
        }
    }

    /**
     * @throws CommonMarkException
     */
    private function convertMarkdownToHtml(string $content): RenderedContentInterface
    {
        $converter = new GithubFlavoredMarkdownConverter(
            [
                'html_input' => 'strip',
                'allow_unsafe_links' => false,
            ]
        );
        $converter->getEnvironment()->addExtension(new AttributesExtension());

        return $converter->convert($content);
    }
}
