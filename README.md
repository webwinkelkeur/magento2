# Webwinkelkeur for Magento® 2

The WebwinkelKeur Connect extension allows seamless integration of your Magento®
2 store with your WebwinkelKeur account. The WebwinkelKeur Foundation is a
non-profit Dutch organisation.

For members of WebwinkelKeur this extension allows the integration of the
innovative WebwinkelKeur Sidebar which increases the overall conversion of the
webshop.

For PLUS members the extension also allows for automatic invitation of customers
to add a review on the WebwinkelKeur page. This extension is recommended for
every WebwinkelKeur member with a Magento® 2 webshop.


## Developed for WebwinkelKeur

WebwinkelKeur is a non-profit quality mark, started due to the discontent with
existing quality marks.  WebwinkelKeur offers a complete customer review system,
where we make sure every review is honest and real.

[Visit WebwinkelKeur](https://www.webwinkelkeur.nl/)


## Development

Configuration for a Magento development environment is included.

Start by bringing up a web and database container using Docker Compose:

    docker-compose up -d

Now install Magento using the included script:

    bin/install-magento

Finally, install the module itself:

    bin/install-module WebwinkelKeur

Or:

    bin/install-module TrustProfile

You should now be able to access Magento on http://localhost:26065/admin and log
in with username `admin` and password `admin123`.