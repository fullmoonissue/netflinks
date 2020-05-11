import {expect, type Page} from '@playwright/test';
import { LinkIndexPage } from './link-index-page';

export class LinkNewPage {
    readonly page: Page;

    constructor(page: Page) {
        this.page = page;
    }

    async goto() {
        await this.page.goto('https://127.0.0.1:8000/admin/fr?crudAction=new&crudControllerFqcn=App%5CInfrastructure%5CController%5CLinkCrudController');
    }

    async createNewLink(
        description?: string,
        url?: string,
        isFavorite: boolean = false,
        hasCategory: boolean = false,
        hasRecipient: boolean = false,
        hasTag: boolean = false,
        hasNote: boolean = false,
    ) {
        if (description !== null) {
            // This will preserve the removal of any new link created during playwright tests
            // (when LinkIndexPage.clean() is called)
            if (description !== LinkIndexPage.description) {
                console.log('Description changed from ' + description + ' to ' + LinkIndexPage.description);
                description = LinkIndexPage.description
            }

            await this.page.getByLabel('Description').click();
            await this.page.getByLabel('Description').fill(description);
        }

        if (url !== null) {
            await this.page.getByLabel('Url').click();
            await this.page.getByLabel('Url').fill(url);
        }

        if (isFavorite) {
            await this.page.getByLabel('Est favori').check();
        }

        if (hasCategory) {
            await this.page.getByRole('combobox', {name: 'Catégorie'}).click();
            await this.page.locator('#Link_category-opt-4').click();
        }

        if (hasRecipient) {
            await this.page.getByRole('combobox', {name: 'Destinataires'}).getByRole('textbox').click();
            await this.page.locator('#Link_recipients-opt-2').click();
            await this.page.getByRole('combobox', {name: 'Destinataires'}).click();
        }

        if (hasTag) {
            await this.page.getByRole('combobox', {name: 'Tags'}).getByRole('textbox').click();
            await this.page.locator('#Link_tags-opt-1').click();
            await this.page.getByRole('combobox', {name: 'Tags'}).click();
        }

        if (hasNote) {
            await this.page.getByLabel('Note').click();
            await this.page.getByLabel('Note').fill('Note about this link');
        }

        await this.page.getByRole('button', { name: 'Créer', exact: true }).click();
    }

    async expectPageToBeAddNewLink() {
        expect(this.page.url()).toEqual('https://127.0.0.1:8000/admin/fr?crudAction=new&crudControllerFqcn=App%5CInfrastructure%5CController%5CLinkCrudController');
    }

    async expectNoLinkCreated() {
        // No save done because not enough required details
        await this.expectPageToBeAddNewLink();
    }
}
