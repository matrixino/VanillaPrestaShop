import testContext from '@utils/testContext';
import {expect} from 'chai';

import {
  boApiClientsPage,
  boDashboardPage,
  boLoginPage,
  type BrowserContext,
  type Page,
  utilsPlaywright,
} from '@prestashop-core/ui-testing';

const baseContext: string = 'functional_API_checkEndpoints';

describe('API : Check endpoints', async () => {
  let browserContext: BrowserContext;
  let page: Page;
  let jsonPaths: object;

  before(async function () {
    browserContext = await utilsPlaywright.createBrowserContext(this.browser);
    page = await utilsPlaywright.newTab(browserContext);
  });

  after(async () => {
    await utilsPlaywright.closeBrowserContext(browserContext);
  });

  describe('Check endpoints', async () => {
    it('should login in BO', async function () {
      await testContext.addContextItem(this, 'testIdentifier', 'loginBO', baseContext);

      await boLoginPage.goTo(page, global.BO.URL);
      await boLoginPage.successLogin(page, global.BO.EMAIL, global.BO.PASSWD);

      const pageTitle = await boDashboardPage.getPageTitle(page);
      expect(pageTitle).to.contains(boDashboardPage.pageTitle);
    });

    it('should go to \'Advanced Parameters > API Client\' page', async function () {
      await testContext.addContextItem(this, 'testIdentifier', 'goToAdminAPIPage', baseContext);

      await boDashboardPage.goToSubMenu(
        page,
        boDashboardPage.advancedParametersLink,
        boDashboardPage.adminAPILink,
      );

      const pageTitle = await boApiClientsPage.getPageTitle(page);
      expect(pageTitle).to.eq(boApiClientsPage.pageTitle);
    });

    it('should check that at least one API client is present', async function () {
      await testContext.addContextItem(this, 'testIdentifier', 'checkThatOneAPIClientExists', baseContext);

      const apiClientsNumber = await boApiClientsPage.getNumberOfElementInGrid(page);
      expect(apiClientsNumber).to.be.greaterThanOrEqual(1);
    });

    it('should fetch the documentation in JSON', async function () {
      await testContext.addContextItem(this, 'testIdentifier', 'fetchJSONDocumentation', baseContext);

      const jsonDoc = await boApiClientsPage.getJSONDocumentation(page);
      expect(jsonDoc).to.be.not.equals(null);
      expect(jsonDoc).to.have.property('paths');

      jsonPaths = jsonDoc.paths;
    });

    it('should check endpoints', async function () {
      await testContext.addContextItem(this, 'testIdentifier', 'checkEndpoints', baseContext);

      let endpoints: string[] = [];

      // eslint-disable-next-line no-restricted-syntax
      for (const [endpointPath, endpointsJSON] of Object.entries(jsonPaths)) {
        // eslint-disable-next-line no-restricted-syntax
        for (const [endpointMethod] of Object.entries(endpointsJSON as object)) {
          endpoints.push(`${endpointPath}: ${endpointMethod.toUpperCase()}`);
        }
      }
      endpoints = endpoints.sort();

      expect(endpoints.length).to.be.greaterThan(0);
      // Dear developers, the CI is broken when you update the module ps_apiresources on the Core.
      // It's normal : it's time to add them UI Tests.
      expect(endpoints).to.deep.equals([
        // @todo: add tests
        '/addresses/customer/{addressId}: GET',
        // @todo: add tests
        '/addresses/customer/{addressId}: PATCH',
        // @todo: add tests
        '/addresses/customer: POST',
        // @todo: add tests
        '/addresses/delete: PUT',
        // @todo: add tests
        '/addresses/{addressId}: DELETE',
        // @todo: add tests
        '/addresses: GET',
        // tests/UI/campaigns/functional/API/02_endpoints/01_apiClient/01_getApiClientInfos.ts
        '/api-client/infos: GET',
        // tests/UI/campaigns/functional/API/02_endpoints/01_apiClient/02_deleteApiClientId.ts
        '/api-client/{apiClientId}: DELETE',
        // tests/UI/campaigns/functional/API/02_endpoints/01_apiClient/03_getApiClientId.ts
        '/api-client/{apiClientId}: GET',
        // tests/UI/campaigns/functional/API/02_endpoints/01_apiClient/04_patchApiClientId.ts
        '/api-client/{apiClientId}: PATCH',
        // tests/UI/campaigns/functional/API/02_endpoints/01_apiClient/05_postApiClient.ts
        '/api-client: POST',
        // tests/UI/campaigns/functional/API/02_endpoints/01_apiClient/06_getApiClients.ts
        '/api-clients: GET',
        // tests/UI/campaigns/functional/API/02_endpoints/02_attribute/01_deleteAttributesAttributeId.ts
        '/attributes/attribute/{attributeId}: DELETE',
        // tests/UI/campaigns/functional/API/02_endpoints/02_attribute/02_getAttributesAttributeId.ts
        '/attributes/attribute/{attributeId}: GET',
        // @todo: add tests
        '/attributes/attribute/{attributeId}: PATCH',
        // tests/UI/campaigns/functional/API/02_endpoints/02_attribute/04_postAttributesAttribute.ts
        '/attributes/attribute: POST',
        // @todo: add tests
        '/attributes/attributes/delete: PUT',
        // @todo: add tests
        '/attributes/group/{attributeGroupId}/attributes: GET',
        // tests/UI/campaigns/functional/API/02_endpoints/02_attribute/07_deleteAttributesGroupId.ts
        '/attributes/group/{attributeGroupId}: DELETE',
        // tests/UI/campaigns/functional/API/02_endpoints/02_attribute/08_getAttributesGroupId.ts
        '/attributes/group/{attributeGroupId}: GET',
        // tests/UI/campaigns/functional/API/02_endpoints/02_attribute/09_patchAttributesGroupId.ts
        '/attributes/group/{attributeGroupId}: PATCH',
        // tests/UI/campaigns/functional/API/02_endpoints/02_attribute/10_postAttributesGroup.ts
        '/attributes/group: POST',
        // tests/UI/campaigns/functional/API/02_endpoints/02_attribute/11_putAttributesGroupsDelete.ts
        '/attributes/groups/delete: PUT',
        // tests/UI/campaigns/functional/API/02_endpoints/02_attribute/12_getAttributesGroups.ts
        '/attributes/groups: GET',
        // @todo: add tests
        '/categories/batch/{mode}: DELETE',
        // @todo: add tests
        '/categories/toggle-status: PUT',
        // tests/UI/campaigns/functional/API/02_endpoints/03_category/03_getCategories.ts
        '/categories: GET',
        // tests/UI/campaigns/functional/API/02_endpoints/03_category/04_deleteCategoryIdCover.ts
        '/category/{categoryId}/cover: DELETE',
        // @todo: add tests
        '/category/{categoryId}/status: PATCH',
        // @todo: add tests
        '/category/{categoryId}/thumbnail: DELETE',
        // tests/UI/campaigns/functional/API/02_endpoints/03_category/07_deleteCategoryIdMode.ts
        '/category/{categoryId}/{mode}: DELETE',
        // @todo: add tests
        '/category/{categoryId}: GET',
        // @todo: add tests
        '/category/{categoryId}: PATCH',
        // @todo: add tests
        '/category: POST',
        // @todo: add tests
        '/contacts/{contactId}: GET',
        // @todo: add tests
        '/contacts/{contactId}: PATCH',
        // @todo: add tests
        '/contacts: GET',
        // @todo: add tests
        '/contacts: POST',
        // tests/UI/campaigns/functional/API/02_endpoints/04_customerGroup/01_deleteCustomerGroupsId.ts
        '/customers/group/{customerGroupId}: DELETE',
        // tests/UI/campaigns/functional/API/02_endpoints/04_customerGroup/02_getCustomerGroupsId.ts
        '/customers/group/{customerGroupId}: GET',
        // tests/UI/campaigns/functional/API/02_endpoints/04_customerGroup/03_putCustomerGroupsId.ts
        '/customers/group/{customerGroupId}: PUT',
        // tests/UI/campaigns/functional/API/02_endpoints/04_customerGroup/04_postCustomersGroup.ts
        '/customers/group: POST',
        // tests/UI/campaigns/functional/API/02_endpoints/04_customerGroup/05_getCustomersGroups.ts
        '/customers/groups: GET',
        // @todo: add tests
        '/features/batch: DELETE',
        // @todo: add tests
        '/features/value/{featureValueId}: DELETE',
        // @todo: add tests
        '/features/value/{featureValueId}: GET',
        // @todo: add tests
        '/features/value/{featureValueId}: PATCH',
        // @todo: add tests
        '/features/value: POST',
        // @todo: add tests
        '/features/values/batch: DELETE',
        // @todo: add tests
        '/features/{featureId}/values: GET',
        // @todo: add tests
        '/features/{featureId}: DELETE',
        // @todo: add tests
        '/features/{featureId}: GET',
        // @todo: add tests
        '/features/{featureId}: PATCH',
        // @todo: add tests
        '/features: GET',
        // @todo: add tests
        '/features: POST',
        // @todo: add tests
        '/hook/{hookId}/status: GET',
        // tests/UI/campaigns/functional/API/02_endpoints/05_hook/01_putHookIdStatus.ts
        '/hook/{hookId}/status: PUT',
        // tests/UI/campaigns/functional/API/02_endpoints/05_hook/02_getHookId.ts
        '/hook/{hookId}: GET',
        // tests/UI/campaigns/functional/API/02_endpoints/05_hook/03_getHooks.ts
        '/hooks: GET',
        // tests/UI/campaigns/functional/API/02_endpoints/06_language/01_getLanguages.ts
        '/languages: GET',
        // tests/UI/campaigns/functional/API/02_endpoints/07_module/01_postModuleUploadArchive.ts
        '/module/upload-archive: POST',
        // tests/UI/campaigns/functional/API/02_endpoints/07_module/02_postModuleUploadSource.ts
        '/module/upload-source: POST',
        // tests/UI/campaigns/functional/API/02_endpoints/07_module/03_putModuleTechnicalNameInstall.ts
        '/module/{technicalName}/install: PUT',
        // tests/UI/campaigns/functional/API/02_endpoints/07_module/04_patchModuleTechnicalNameReset.ts
        '/module/{technicalName}/reset: PATCH',
        // tests/UI/campaigns/functional/API/02_endpoints/07_module/05_putModuleTechnicalNameStatus.ts
        '/module/{technicalName}/status: PUT',
        // tests/UI/campaigns/functional/API/02_endpoints/07_module/06_putModuleTechnicalNameUninstall.ts
        '/module/{technicalName}/uninstall: PUT',
        // tests/UI/campaigns/functional/API/02_endpoints/07_module/07_putModuleTechnicalNameUpgrade.ts
        '/module/{technicalName}/upgrade: PUT',
        // tests/UI/campaigns/functional/API/02_endpoints/07_module/08_getModuleTechnicalName.ts
        '/module/{technicalName}: GET',
        // tests/UI/campaigns/functional/API/02_endpoints/07_module/09_putModulesToggleStatus.ts
        '/modules/toggle-status: PUT',
        // tests/UI/campaigns/functional/API/02_endpoints/07_module/10_putModulesUninstall.ts
        '/modules/uninstall: PUT',
        // tests/UI/campaigns/functional/API/02_endpoints/07_module/11_getModules.ts
        '/modules: GET',
        // tests/UI/campaigns/functional/API/02_endpoints/08_product/01_deleteProductImageId.ts
        '/product/image/{imageId}: DELETE',
        // tests/UI/campaigns/functional/API/02_endpoints/08_product/02_getProductImageId.ts
        '/product/image/{imageId}: GET',
        // tests/UI/campaigns/functional/API/02_endpoints/08_product/03_postProductImageId.ts
        '/product/image/{imageId}: POST',
        // tests/UI/campaigns/functional/API/02_endpoints/08_product/04_postProductIdImage.ts
        '/product/{productId}/image: POST',
        // tests/UI/campaigns/functional/API/02_endpoints/08_product/05_getProductIdImages.ts
        '/product/{productId}/images: GET',
        // tests/UI/campaigns/functional/API/02_endpoints/08_product/06_patchProductIdShops.ts
        '/product/{productId}/shops: PATCH',
        // tests/UI/campaigns/functional/API/02_endpoints/08_product/07_deleteProductId.ts
        '/product/{productId}: DELETE',
        // tests/UI/campaigns/functional/API/02_endpoints/08_product/08_getProductId.ts
        '/product/{productId}: GET',
        // tests/UI/campaigns/functional/API/02_endpoints/08_product/09_patchProductId.ts
        '/product/{productId}: PATCH',
        // tests/UI/campaigns/functional/API/02_endpoints/08_product/10_postProduct.ts
        '/product: POST',
        // tests/UI/campaigns/functional/API/02_endpoints/08_product/11_getProductsSearch.ts
        '/products/search: GET',
        // tests/UI/campaigns/functional/API/02_endpoints/08_product/12_getProducts.ts
        '/products: GET',
        // tests/UI/campaigns/functional/API/02_endpoints/09_searchAlias/01_deleteSearch.ts
        '/search-alias/{search}: DELETE',
        // @todo: add tests
        '/search-alias/{search}: GET',
        // @todo: add tests
        '/search-alias/{search}: PUT',
        // @todo: add tests
        '/search-alias: POST',
        // @todo: add tests
        '/search-aliases/delete: DELETE',
        // @todo: add tests
        '/search-aliases: GET',
        // @todo: add tests
        '/store/{storeId}/toggle-status: PUT',
        // @todo: add tests
        '/store/{storeId}: DELETE',
        // @todo: add tests
        '/store/{storeId}: GET',
        // @todo: add tests
        '/stores/delete: PUT',
        // @todo: add tests
        '/stores/set-status: PUT',
        // @todo: add tests
        '/supplier/{supplierId}/logo: DELETE',
        // @todo: add tests
        '/supplier/{supplierId}/toggle-status: PUT',
        // @todo: add tests
        '/supplier/{supplierId}: DELETE',
        // @todo: add tests
        '/supplier/{supplierId}: GET',
        // @todo: add tests
        '/supplier/{supplierId}: PATCH',
        // @todo: add tests
        '/supplier: POST',
        // @todo: add tests
        '/suppliers/delete: PUT',
        // @todo: add tests
        '/suppliers/disable: PUT',
        // @todo: add tests
        '/suppliers/enable: PUT',
        // @todo: add tests
        '/suppliers: GET',
        // @todo: add tests
        '/tab/{className}: PUT',
        // @todo: add tests
        '/tax-rules-group/{taxRulesGroupId}: DELETE',
        // @todo: add tests
        '/tax-rules-group/{taxRulesGroupId}: GET',
        // @todo: add tests
        '/tax-rules-group/{taxRulesGroupId}: PATCH',
        // @todo: add tests
        '/tax-rules-group: POST',
        // @todo: add tests
        '/tax-rules-groups/delete: PUT',
        // @todo: add tests
        '/tax-rules-groups/set-status: PUT',
        // @todo: add tests
        '/tax-rules-groups: GET',
        // @todo: add tests
        '/title/{titleId}: DELETE',
        // @todo: add tests
        '/title/{titleId}: GET',
        // @todo: add tests
        '/title/{titleId}: PATCH',
        // @todo: add tests
        '/title: POST',
        // @todo: add tests
        '/titles/delete: PUT',
        // @todo: add tests
        '/titles: GET',
        // @todo: add tests
        '/webservice-key/{webserviceKeyId}: GET',
        // @todo: add tests
        '/webservice-key/{webserviceKeyId}: PATCH',
        // @todo: add tests
        '/webservice-key: POST',
        // @todo: add tests
        '/webservice-keys: GET',
        // @todo: add tests
        '/zone/{zoneId}/toggle-status: PUT',
        // @todo: add tests
        '/zone/{zoneId}: DELETE',
        // @todo: add tests
        '/zone/{zoneId}: GET',
        // @todo: add tests
        '/zone/{zoneId}: PUT',
        // @todo: add tests
        '/zone: POST',
        // @todo: add tests
        '/zones/delete: PUT',
        // @todo: add tests
        '/zones/toggle-status: PUT',
        // tests/UI/campaigns/functional/API/02_endpoints/16_zone/08_getZones.ts
        '/zones: GET',
      ]);
    });
  });
});
