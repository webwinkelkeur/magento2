package_name=`echo $(platform) | tr A-Z a-z`
version=`git describe --tags --match "v*"`

xmls:
	for file in `find common/etc -name "*xml.php"`; do \
	    php $$file $(platform) > "$${file%.*}"; \
	done

composer:
	cd $(package_name) && sed -i.bak 's/$$VERSION/'"$(version)"'/g' composer.json  \

package: xmls composer
	cd $(package_name) && zip -rq -X "../dist/$(package_name)-magento2-$(version).zip" .;
	git clean -f