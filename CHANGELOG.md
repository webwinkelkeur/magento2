# Changelog

## 2.3.4 - 2026-07-28

- Allow dashboard-hosted images through Magento's Content Security Policy by
  whitelisting the dashboard origin for `img-src`.

## 2.3.3 - 2026-04-16

- Whitelist the entire dashboard origin for sidebar scripts and fonts instead
  of specific asset paths.

## 2.3.2 - 2026-02-19

- Add a setting for selecting the store views to which product reviews are
  synced, falling back to the originating store view.
- Generate product review sync URLs in the correct store scope.

## 2.3.1 - 2025-11-18

- Save synced product reviews only to the store view passed in the sync URL.

## 2.3.0 - 2025-10-31

- Include a store-aware review sync URL for each product in invitation data.
- Use the store from the sync URL when selecting the configuration used to
  authorize product review synchronization.

## 2.2.9 - 2025-07-16

- Add the reviewed organization to aggregate-rating structured data.
- Include the order total in invitation data.
- Escape dynamic output in storefront and admin templates.
- Allow dashboard-hosted frames through Magento's Content Security Policy.

## 2.2.8 - 2025-02-05

- Report invitations as disabled when the extension itself is disabled.

## 2.2.7 - 2025-01-14

- Reject product review synchronization when no rating options are configured.

## 2.2.6 - 2025-01-07

- Log product review deletions.

## 2.2.5 - 2024-11-21

- Include product data for visible parent order items in invitation requests.
- Continue creating invitations if product data cannot be retrieved.

## 2.2.4 - 2024-10-02

- Include the extension and Magento platform versions in invitation requests.
- Set the module version from the Git tag when building packages.

## 2.2.3 - 2024-07-03

- Add CSP whitelist entries for the sidebar script and font.
- Remove the direct nonce-provider dependency for compatibility with Magento
  versions that do not provide it.

## 2.2.2 - 2024-07-01

- Render the sidebar bootstrap script with Magento's secure HTML renderer and a
  CSP nonce.

## 2.2.1 - 2024-04-17

- Preserve the original creation date when synchronizing product reviews.
- Use Magento's review resource model when saving and deleting reviews.

## 2.2.0 - 2024-02-08

- Load invitation product data in the order's store view so localized product
  URLs are used.
- Fix the PHP 8 development container and add its required extensions.

## 2.1.9 - 2023-09-15

- Update the TrustProfile logo and brand icon.

## 2.1.8 - 2023-09-12

- Add an optional consent flow that exposes order data on the checkout success
  page and creates invitations only after consent is recorded.
- Add TrustProfile manual-install packages and installation instructions.
- Reuse cURL connections for dashboard API requests.

## 2.1.7 - 2023-09-01

- Support PHP 7.2.
- Fix the development installer for Magento 2.0 through 2.3.

## 2.1.6 - 2023-01-30

- Use Magento's product review entity ID and the product's store assignments
  when saving synchronized reviews.

## 2.1.5 - 2023-01-03

- Add product review synchronization, including rating-option and GTIN
  configuration.
- Include product metadata in invitation requests and register the product
  review sync URL with the dashboard.

## 2.1.4 - 2022-07-01

- Add PHP 8 support.

## 2.1.3 - 2022-05-18

- Generate a separate ZIP package for manual installation.
- Update the WebwinkelKeur user guide and release packaging.

## 2.1.2 - 2021-09-14

- Add the TrustProfile-branded extension generated from shared module code.
- Add project-specific build, development, installation, and packaging tools.
- Update the TrustProfile dashboard and public domains.

## 2.1.0 - 2020-09-15

- Update the sidebar script to make only one HTTP request.

## 2.0.1 - 2020-03-24

- Rename the module and namespace to `WebwinkelKeur_Magento2`.
- Fix orders without a shipping address, such as gift-card orders.
- Add release packaging and installation documentation.

## 2.0.0 - 2020-03-02

- Publish the extension as the `webwinkelkeur/magento2` Composer package while
  replacing the previous package.
- Remove Magmodules branding from the extension configuration.

## 1.0.4 - 2017-10-20

- Various fixes by Magmodules. [View changes on
  GitHub.](https://github.com/webwinkelkeur/magento2/compare/1.0.3..1.0.4)

## 1.0.3 - 2017-08-09

- Fixes for Magento v2.2.

## 1.0.2 - 2017-02-24

- Update `composer.json`.
- Update copyright information.

## 1.0.1 - 2017-01-17

- Rewrite the review import function to use AJAX.
- Fix the widget when using the WYSIWYG editor.

## 1.0.0

- First stable release.

## 0.9.0

- Initial beta release.
