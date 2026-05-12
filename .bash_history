composer create-project codeigniter4/appstarter ci4-temp
apt-get update && apt-get install -y libicu-dev && docker-php-ext-install intl
exit
composer create-project codeigniter4/appstarter ci4-temp
rm -rf ci4-temp
composer create-project codeigniter4/appstarter ci4-temp
cp -a ci4-temp/. .
rm -rf ci4-temp
ls -la
composer require aws/aws-sdk-php
cp env .env
docker exec -it tixcloud-app bash
php spark migrate
exit
php spark migrate
exit
php spark migrate
exit
