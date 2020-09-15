= Release instructions =

== GitHub ==

* Update `CHANGELOG.md`.
* Tag the master branch:

~~~
git tag v1.2.3
~~~

* Make a package:

~~~
./bin/package
~~~

* Test the package.
* Release the new version:

~~~
./bin/publish
~~~

* Edit the release on GitHub to match `CHANGELOG.md`.

== Magento Marketplace ==

* Navigate to the extension on [Magento
  Marketplace](https://developer.magento.com/extensions/versions/webwinkelkeur-magento2).
* Create a new version.
* Upload the package.
* Set release notes to match `CHANGELOG.md`.
* Submit and wait for review.
