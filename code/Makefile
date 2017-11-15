.PHONY: build

.DEFAULT_GOAL := build

CSS_DESTINATION := web/main.css
CSS_SOURCE := src/WebInterface/Presentation/Http/StyleSheet/unstructured.css
JS_DESTINATION := web/main.js
JS_SOURCE := $(shell find src/WebInterface/Presentation/Http/JavaScript -type f \( -name '*.js' ! -name '*bootstrap.js' \)) \
	src/WebInterface/Presentation/Http/JavaScript/bootstrap.js

build: $(JS_DESTINATION) \
	$(CSS_DESTINATION)

$(JS_DESTINATION): $(JS_SOURCE)
	@echo Compile $@
	@mkdir -p $(@D)
	@cat $^ > $@

$(CSS_DESTINATION): $(CSS_SOURCE)
	@echo Compile $@
	@mkdir -p $(@D)
	@cat $^ > $@
