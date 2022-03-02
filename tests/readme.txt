./vendor/bin/phpunit tests --colors --exclude-group integration --coverage-html tests/coverage --coverage-filter ./src
./vendor/bin/psalm --no-cache
./vendor/bin/psalm --taint-analysis