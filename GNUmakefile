SHELL = bash

PROJECTS := $(patsubst %/composer.json,%,$(shell git ls-files '*/composer.json'))
XML_TEMPLATES := $(patsubst common/%,%,$(shell git ls-files 'common/**.xml'))
XML_TARGETS := $(foreach project,$(PROJECTS),$(patsubst %,$(project)/%,$(XML_TEMPLATES)))
COPY_SOURCES := $(patsubst common/%,%,$(shell git ls-files 'common/view'))
COPY_TARGETS := $(foreach project,$(PROJECTS),$(patsubst %,$(project)/%,$(COPY_SOURCES)))
CLASS_BASES := $(shell git ls-files 'common/Controller/**.php')
CLASS_TARGETS := $(foreach project,$(PROJECTS),$(patsubst common/%,$(project)/%,$(CLASS_BASES)))

all : xmls copies classes autoloaders
.PHONY : all

clean :
	git clean -dfX $(PROJECTS)
.PHONY : clean

xmls : $(XML_TARGETS)
.PHONY : xmls

copies : $(COPY_TARGETS)
.PHONY : copies

classes : $(CLASS_TARGETS)
.PHONY : classes

autoloaders : $(patsubst %,%/vendor/autoload.php,$(PROJECTS))
.PHONY : autoloaders

%/vendor/autoload.php : %/composer.json
	cd $* && composer dump-autoload

define PROJECT_RULES
$(1)/%.xml : common/%.xml
	@mkdir -p $$(dir $$@)
	bin/templated-xml $(1) common/$$*.xml > $$@~
	@mv $$@~ $$@

$(1)/%.php : common/%.php
	@mkdir -p $$(dir $$@)
	bin/extend-base-class $(1) $$< > $$@~
	@mv $$@~ $$@

$(1)/% : common/%
	@mkdir -p $$(dir $$@)
	cp $$< $$@
endef

$(foreach project,$(PROJECTS),$(eval $(call PROJECT_RULES,$(project))))
