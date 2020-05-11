import { expect, type Page } from '@playwright/test';

export class LinkIndexPage {
    readonly page: Page;
    public static readonly description: string = 'Playwright Test Link';
    public static readonly url: string = 'https://www.perdu.com';

    constructor(page: Page) {
        this.page = page;
    }

    async gotoListAll() {
        await this.page.goto('https://127.0.0.1:8000/admin/fr?crudAction=index&crudControllerFqcn=App%5CInfrastructure%5CController%5CLinkCrudController');
    }

    async gotoListFavorites() {
        await this.page.goto('https://127.0.0.1:8000/admin/fr?crudAction=index&crudControllerFqcn=App%5CInfrastructure%5CController%5CLinkCrudController&filters%5BisFavorite%5D=1');
    }

    async getLinkCount() {
        return parseInt(await this.getTotalLinkCountLocator().textContent());
    }

    async clean() {
        await this.gotoListAll();
        if (await this.getFirstLinkDescription() === LinkIndexPage.description) {
            await this.getFirstLinkCheckboxLocator().click();
            await this.getDeleteLinkLocator().click();
            await this.getProceedDeleteButtonLocator().click();
        }
    }

    async expectTotalCountIncremented(currentLinkCount: number) {
        await expect(this.getTotalLinkCountLocator()).toHaveText(String(currentLinkCount + 1));
    }

    async expectFirstLinkToHaveAssignedDescription(testLinkDescription: string) {
        await expect(this.getFirstLinkLineLocator()).toContainText(testLinkDescription);
    }

    async expectNewLinkCreated(linkCount: number, linkDescription: string, favoriteLinkCount: number = null) {
        await this.expectPageToBeListLinks();

        await this.expectTotalCountIncremented(linkCount);
        await this.expectFirstLinkToHaveAssignedDescription(linkDescription);

        if (null !== favoriteLinkCount) {
            await this.gotoListFavorites();

            await this.expectTotalCountIncremented(favoriteLinkCount);
            await this.expectFirstLinkToHaveAssignedDescription(linkDescription);
        }
    }

    async expectPageToBeListLinks() {
        expect(this.page.url()).toEqual('https://127.0.0.1:8000/?crudAction=index&crudControllerFqcn=App%5CInfrastructure%5CController%5CLinkCrudController');
    }

    private getTotalLinkCountLocator() {
        return this.page.locator('div.list-pagination-counter strong');
    }

    private getFirstLinkLineLocator() {
        return this.page.locator('#main tbody tr').first();
    }

    private getFirstLinkCheckboxLocator() {
        return this.page.locator('input#form-batch-checkbox-0');
    }

    private getDeleteLinkLocator() {
        return this.page.locator('a.action-batchDelete');
    }

    private getProceedDeleteButtonLocator() {
        return this.page.locator('button#modal-batch-action-button');
    }

    private getFirstLinkDescription() {
        return this.getFirstLinkLineLocator().locator('td').locator('nth=1').locator('span').innerText();
    }
}
