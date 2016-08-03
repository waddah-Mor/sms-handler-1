.PHONY: all dependency-install unit-tests image-build

all: dependency-install unit-tests image-build

dependency-install:
	/usr/local/bin/composer update

unit-tests:
	./vendor/bin/phpunit

image-build:
	@echo "Building Image";
	@read -p "Enter Tag[]: " image_tag; \
		docker build -t $$image_tag .