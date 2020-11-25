xmls:
	for file in `find common/etc -name "*xml.php"`; do \
	    php $$file $(platform) > "$${file%.*}"; \
	done

package: xmls
	version=`git describe --tags --match "v*"`; \
	package_name=`echo $(platform) | tr A-Z a-z`; \
	tar --exclude='*.xml.php' -zchvf  dist/$$pal-$$version.tgz `echo $(platform) | tr A-Z a-z`/