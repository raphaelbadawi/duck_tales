# Duck Tales

## Purpose

This is a basic Twitter clone, but instead of birds we have ducks.

## Install

- clone the repo
- install PHP dependencies `composer install`
- install JS dependencies `yarn install` (you may also use npm)
- run `yarn watch` to transpile CSS/JS assets
- run `symfony server:start` to launch the dev server (make sure you have installed Symfony globally via Composer)
- run `docker-compose up` in order for ElasticSearch to work

## Configuration

- in order for the database to work, please set up the DATABASE_URL field in .env, we recommand a sqlite driver in a dev environment (for example `DATABASE_URL="sqlite:///%kernel.project_dir%/var/data.db"`)
- in order for mails to work, please fill in the MAILER_DSN field with SMTP provider URI in .env
- in order for Google OAuth to work, please fulfill OAUTH_GOOGLE_ID and OAUTH_GOOFLE_SECRET fields in .env with your Google OAuth keys
- if you use ElasticSearch, please set the ELASTICSEARCH_ENDPOINT in the .env