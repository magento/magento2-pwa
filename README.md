# Overview

This module is for additional extensions in core Magento to work with PWA Studio. To provide the ability to develop the project locally, we introduced a development workflow that can help external developers work with the project.

## Development setup

### Installation as a git-based composer package

1. Clone and/or navigate to your [`magento2` git repository](https://github.com/magento/magento2) and check out the latest develop branch, e.g. `2.4-develop`. You may also check out and use any `2.4` release tags.

    ```bash
    git clone git@github.com:magento/magento2.git
    cd magento2
    ```

1. Create an `ext` directory within the root of your `magento2` project:

    ```bash
    mkdir ext
    ```

1. Clone the `magento2-pwa` repository into you vendor directory name:

    ```bash
    git clone git@github.com:magento-commerce/magento2-pwa.git ext/magento/magento2-pwa
    ```

1. Update the `magento2/composer.json` settings to create a better development workflow for your extension modules:

    -  Update the `minimum-stability` for packages to `dev`. This allows for the installation of development modules:

        ```bash
        composer config minimum-stability dev
        ```

    - To work with `stable` packages, ensure that the `prefer-stable` property is `true`. This property should already be included in the `composer.json` file, right above the `minimum-stability` setting.

    -  Configure `composer` so that it knows where to find new modules. The following command will configure any extension code inside the ext directory to be treated as a package and symlinked to the vendor directory:

        ```bash
        composer config repositories.ext path "./ext/*/*/*"
        ```

1. Finally, install your extension module:

    ```bash
    composer require [module name]
    ```

At this point, the module is symlinked inside the vendor directory, which allows both running a Magento installation with additional modules as well as doing development using the standard git workflow.

You may need to ensure that Magento2-pwa\* modules is listed as enabled when you run bin/magento module:status. If they are, [follow the docs on how to enable modules](https://devdocs.magento.com/guides/v2.3/extension-dev-guide/build/enable-module.html).

### Setting up Git workflow

To improve the developer experience when working with this repository structure, a few additional items may be configured:

1. Exclude `ext/` directories in the project's `.git` directory:

    ```bash
    echo ext >> ./.git/info/exclude
    ```

1. Skip root directory `composer.\*` files to avoid committing them by mistake:

    ```bash
    git update-index --skip-worktree composer.json
    git update-index --skip-worktree composer.lock
    ```

    You can reverse this operation as needed:

    ```bash
    git update-index --no-skip-worktree composer.json
    git update-index --no-skip-worktree composer.lock
    ```

## Cloud deployment extension installation

1. Add https://repo.magento.com as a composer repository by adding the following to your cloud instances `composer.json` file.

    ```json
    "repositories": {
        "repo": {
            "type": "composer",
            "url": "https://repo.magento.com"
        }
    },
    ```

1. Require in `magento/magento2-pwa` extension by adding the following to your cloud instances `composer.json` file.

    ```json
    "require": {
        "magento/magento2-pwa": "0.0.1"
    },
    ```

1. Ensure your `auth.json` file has valid credential for `repo.magento.com`.

1. Run `composer update` to update your `composer.lock` file.

1. Push the changes and deploy your cloud instance.
