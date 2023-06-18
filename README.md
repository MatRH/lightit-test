1) git clone git@github.com:MatRH/lightit-test.git 

2) cd lightit-test

3) docker run --rm \
    -u "$(id -u):$(id -g)" \
    -v "$(pwd):/var/www/html" \
    -w /var/www/html \
    laravelsail/php82-composer:latest \
    composer install --ignore-platform-reqs

4) cp .env.example .env

5) ./vendor/bin/sail up -d

6) sudo apt install php-mysql

7) ./vendor/bin/sail artisan migrate

    A) IF YOU GET A MYSQL ERROR RELATED TO THE SAIL USER IN THE DB RUN THE FOLLOWING COMMANDS:
        I) ./vendor/bin/sail down -v
        I) ./vendor/bin/sail artisan migrate

8) The app should now be running on http://localhost/

EMAILS:
    2) You can dispatch the email queue by running ./vendor/bin/sail artisan queue:work
    2) You can check the mock emails sent by accessing http://localhost:8025/


