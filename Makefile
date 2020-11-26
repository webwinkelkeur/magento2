package_name=`echo $(platform) | tr A-Z a-z`
version=`git describe --tags --match "v*"`

all: xmls package composer
.PHONY: all

xmls:
	for file in `find common/etc -name "*xml.php"`; do \
	    php $$file $(platform) > "$${file%.*}"; \
	done

package:
	cd $(package_name) && zip -rq -X "../dist/$(package_name)-magento2-$(version).zip" . -x "*.xml.php";
	git clean -f; \

composer:
	cd "dist" && xargs -r -d'\n' perl -p -i -e 's/\$VERSION\$/'"$version"'/g'; \
