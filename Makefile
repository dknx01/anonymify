.DEFAULT_GOAL := help
.PHONY: help

help: ## show this help
	@printf "%-20s %s\n" "Target" "Description"
	@printf "%-20s %s\n" "------" "-----------"
	@sed -rn 's/^([a-zA-Z_-]+):.*?## (.*)$$/"\1" "\2"/p' < $(MAKEFILE_LIST) | sort | xargs printf "%-20s %s\n"

phpunit: ## run PHPUnit
	vendor/bin/phpunit
php-cs-fix: ## run PHP-CS-Fixer
	vendor/bin/php-cs-fixer fix
phpstan: ## run PHPStan
	vendor/bin/phpstan
qa: php-cs-fix phpstan phpunit deptrac ## run all qa steps

deptrac: ## run deptrac for architecture dependency analyse
	vendor/bin/deptrac --report-uncovered
