SHELL = bash

PROJECTS := TrustProfile WebwinkelKeur
XML_TEMPLATES := $(shell find common -type f -name '*.xml')
XML_TARGETS := $(foreach project,$(PROJECTS),$(patsubst common/%,$(project)/%,$(XML_TEMPLATES)))
COPY_SOURCES := $(shell find common/view -type f)
COPY_TARGETS := $(foreach project,$(PROJECTS),$(patsubst common/%,$(project)/%,$(COPY_SOURCES)))
CLASS_BASES := $(shell find common/Controller -type f -name '*.php')
CLASS_TARGETS := $(foreach project,$(PROJECTS),$(patsubst common/%,$(project)/%,$(CLASS_BASES)))

all : $(XML_TARGETS) $(COPY_TARGETS) $(CLASS_TARGETS)
.PHONY : all

clean :
	git clean -df $(PROJECTS)
.PHONY : clean

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

phpstan : tools/phpstan/vendor/.updated
	tools/phpstan/vendor/bin/phpstan analyse common $(PROJECTS)
.PHONY : phpstan

tools/phpstan/vendor/.updated :
	composer -d tools/phpstan install
	touch $@
