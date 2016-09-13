# Front-End Code Overview

## Project Tree

    dist/craft    # The Craft CMS lives here
                  # You must add the 'app' folder to this directory
      - config    # The Craft configuration files live here
    dist/public   # This will be your webroot
      - assets   
        - css     # The compiled CSS files
        - img     # Optimized image files
        - js      # Concatenated Javascript files
        - webfonts # Locally-hosted webfont files
    src
      - img       # Unoptimized image files, as exported from Photoshop, etc.
      - js        # All custom scripts for the theme
        - jspm_modules # 3rd-party vendor script libraries
        - modules # Individual components providing site-specific functionality
        - plugins # Self-contained generic plugins (not site-specific)
        - site    # Custom-written plugins to perform specific tasks
        - vendor  # 3rd-party vendor script libraries
        - app.js  # Entry-point for the site's JS -
                  # you should import everything else from here
      - scss      # SCSS source files
                  # Autoprefixer will add browser support as appropriate
      - templates # HTML/PHP/Twig templates
      - webfonts  # Any webfont files here will be copied verbatim to the dist folder

## Front-End Build Process

### Initial Setup

The front-end build process requires Node v4.0.0 or higher. Once Node is installed, run the following command from the project root to install all the build-process dependancies:

    npm install

We use [JSPM](http://jspm.io) to manage all front-end JavaScript dependencies. Run `jspm install [package-name]` to install a new component. Please [read the docs](https://github.com/jspm/jspm-cli/blob/master/docs/installing-packages.md) for details on getting a particular version or installing from various sources. To install these dependencies:

    jspm install

### Building Front-End Assets

Use Gulp to compile SASS source files to CSS, minify JS and CSS, and optimize images. Use `npm run [command]` to run the Gulp scripts. To start, run:

    npm run build

While actively developing, watch source files and rebuild as needed. This command will open a new browser tab using BrowserSync, which will automatically update to reflect changes you make to the source files.

    npm run watch

To generate minified copies of all the assets without sourcemaps for production, run the following command:

    npm run build --production

## Install PHP Environment Configuration Package

To manage running Craft in multiple environments and keep sensitive passwords out of the repository, we run phpdotenv. To install, run the following commands from the project root:

    curl -s http://getcomposer.org/installer | php
    php composer.phar require vlucas/phpdotenv

Once installed, copy `dist/.env.example` to `dist/.env` and edit the new file for your developemnt environment.

## Starting a Docker server

The local server uses [Docker Compose](http://docs.docker.com/compose/). If you are running OS X we recommend installing [Docker for Mac](https://docs.docker.com/docker-for-mac/). Once you have that set up, run the following commands to control the server:

    docker-compose up -d  # Start the server
    docker-compose down   # Stop the server

You will probably want to add Docker's IP address to your `/etc/hosts` file as well for easy access. The virtual host domain name is preset to `docker.dev`, but you can easily change it by modifying the second line of `docker/vhost.conf`. Make sure the domain name you set there matches the domain name in your `hosts` file.

You can connect to the database using an app such as [Sequel Pro](http://sequelpro.com). Use the following settings:

    Host:     [Docker's external IP]
    Username: app
    Password: 123
    Database: app

## Normal Development Process

The following outlines the normal development workflow for this project:

1. Open a terminal window and navigate to the project directory
2. Start the Docker server by running:

    docker-compose up -d

3. Run the initial build process to get everything in place:

    npm run build

4. Kick off the watch build process to start watching the src files and open the dev site in a browser window

    npm run watch

5. Start making your edits to the styles, javascript or templates.
6. If the watch process errors out, check the terminal output for what caused the error. Correct the error and restart the watch process.
7. When done, hit `CTL - C` in the terminal window to stop the watch process.
8. Shut down the Docker server by running:

    docker-compose down


## Specific Notes

Most project-specific build configuration can be accomplished via the `gulpfile.babel.js/config.js` file.

### Dev Environment

Before running Docker or Docker Compose, make sure you host environment is not currently running Apache, PHP or MySQL. If any of these services are running and mounted on their standard ports, the Docker Containers will fail to run.

### Required Files and Folders Not In This Repository

This repository excludes the Craft CMS `apps` folder, as well as the public `images` and `files` folders. Download the current version of Craft, extract the `craft/apps` folder and move it to `dist/craft`. Download the `images` and `files` public folders from the production server and move them to `dist/public`.

### CSS

We run Autoprefixer on the compiled CSS files to add additional browser support wherever possible.

### Scripts

Javascript files will be compiled from the `src/js` folder. Any ES6 features will be transcoded to their ES5 equivalent. Use ES2015-style module imports.

### SVGs

SVG files saved to `assets/src/images` will be compressed and output to `assets/dist/images`.
