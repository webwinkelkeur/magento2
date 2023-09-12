# Manual installation

1. Extract the package for manual installation in your Magento installation
   root. Alternatively, extract the package elsewhere and copy the contents of
   the `app/code/` directory to the corresponding directory in your Magento
   installation.

1. Run `bin/magento setup:upgrade`

1. Run `bin/magento module:enable TrustProfile_Magento2`

1. Check the module status with `bin/magento module:status
   TrustProfile_Magento2`

1. Navigate to Stores > Configuration and select _TrustProfile_ in the menu to
   configure the extension.
