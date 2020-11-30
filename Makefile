SHELL = bash

PROJECTS := $(patsubst %/composer.json,%,$(shell git ls-files '*/composer.json'))
XML_TEMPLATES := $(patsubst common/%,%,$(shell git ls-files 'common/etc/**.xml.php'))
XML_PLAIN := $(patsubst common/%,%,$(shell git ls-files 'common/etc/**.xml'))
XML_TARGET_NAMES := $(XML_PLAIN) $(patsubst %.php,%,$(XML_TEMPLATES))
XML_TARGETS := $(foreach project,$(PROJECTS),$(patsubst %,$(project)/%,$(XML_TARGET_NAMES)))

all : xmls autoloaders
.PHONY : all

xmls : $(XML_TARGETS)
.PHONY : xmls

autoloaders : $(patsubst %,%/vendor/autoload.php,$(PROJECTS))
.PHONY : autoloaders

%/vendor/autoload.php : %/composer.json
	cd $* && composer dump-autoload

define PROJECT_RULES
$(1)/etc/%.xml : common/etc/%.xml.php
	mkdir -p $$(dir $$@)
	php common/etc/$$*.xml.php $(1) > $$@~
	mv $$@~ $$@
$(1)/etc/%.xml : common/etc/%.xml
	mkdir -p $$(dir $$@)
	cp common/etc/$$*.xml $$@
endef

$(foreach project,$(PROJECTS),$(eval $(call PROJECT_RULES,$(project))))
