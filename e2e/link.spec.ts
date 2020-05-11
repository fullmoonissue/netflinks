import { test } from '@playwright/test';
import { LinkIndexPage } from './link-index-page';
import { LinkNewPage } from './link-new-page';

test.describe('New Link', () => {
    let linkIndexPage: LinkIndexPage;
    let linkNewPage: LinkNewPage;

    const testLinkDescription = LinkIndexPage.description;
    const testLinkUrl = LinkIndexPage.url;

    let linkCount: number;
    let favoriteLinkCount: number;

    test.beforeEach(async ({page}) => {
        test.setTimeout(3000);

        linkIndexPage = new LinkIndexPage(page);

        // Retrieve current link's count
        await linkIndexPage.gotoListAll();
        linkCount = await linkIndexPage.getLinkCount();

        // Retrieve current favorite link's count
        await linkIndexPage.gotoListFavorites();
        favoriteLinkCount = await linkIndexPage.getLinkCount();

        // Let's go the "New Link" page
        linkNewPage = new LinkNewPage(page);
        await linkNewPage.goto();
    });

    test.afterEach(async () => {
        await linkIndexPage.clean();
    });

    test('saves a link with only description', async () => {
        await linkNewPage.createNewLink(
            testLinkDescription,
            null,
        );

        await linkNewPage.expectNoLinkCreated();
    });

    test('saves a link with only url', async () => {
        await linkNewPage.createNewLink(
            null,
            testLinkUrl,
        );

        await linkNewPage.expectNoLinkCreated();
    });

    test('saves a link with minimum details', async () => {
        await linkNewPage.createNewLink(
            testLinkDescription,
            testLinkUrl,
        );

        await linkIndexPage.expectNewLinkCreated(linkCount, testLinkDescription);
    });

    test('saves a link as favorite', async () => {
        await linkNewPage.createNewLink(
            testLinkDescription,
            testLinkUrl,
            true,
        );

        await linkIndexPage.expectNewLinkCreated(linkCount, testLinkDescription, favoriteLinkCount);
    });

    test('saves a link with a category', async () => {
        await linkNewPage.createNewLink(
            testLinkDescription,
            testLinkUrl,
            false,
            true, // hasCategory
        );

        await linkIndexPage.expectNewLinkCreated(linkCount, testLinkDescription);
    });

    test('saves a link with a recipient', async () => {
        await linkNewPage.createNewLink(
            testLinkDescription,
            testLinkUrl,
            false,
            false,
            true, // hasRecipient
        );

        await linkIndexPage.expectNewLinkCreated(linkCount, testLinkDescription);
    });

    test('saves a link with a tag', async () => {
        await linkNewPage.createNewLink(
            testLinkDescription,
            testLinkUrl,
            false,
            false,
            false,
            true, // hasTag
        );

        await linkIndexPage.expectNewLinkCreated(linkCount, testLinkDescription);
    });

    test('saves a link with a note', async () => {
        await linkNewPage.createNewLink(
            testLinkDescription,
            testLinkUrl,
            false,
            false,
            false,
            false,
            true, // hasNote
        );

        await linkIndexPage.expectNewLinkCreated(linkCount, testLinkDescription);
    });

    test('saves a link with maximum details', async () => {
        await linkNewPage.createNewLink(
            testLinkDescription,
            testLinkUrl,
            true,
            true,
            true,
            true,
            true,
        );

        await linkIndexPage.expectNewLinkCreated(linkCount, testLinkDescription, favoriteLinkCount);
    });
});
