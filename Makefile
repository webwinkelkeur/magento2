xmls:
	for file in `find common/etc -name "*xml.php"`; do \
	    php $$file $(platform) > "$${file%.*}"; \
	done

package: xmls
	version=`git describe --tags --match "v*"`; \
	package_name=`echo $(platform) | tr A-Z a-z`; \
	cd $$package_name && zip -r -X "../dist/$$package_name-magento2-$$version.zip" .; \