# Puzzle Admin Newsletter Bundle
**=========================**

Puzzle bundle for managing admin 

### Step 1: Download the Bundle

Open a command console, enter your project directory and execute the following command to download the latest stable version of this bundle:

`composer require webundle/puzzle-admin-newsletter`

### Step 2: Enable the Bundle

Then, enable the bundle by adding it to the list of registered bundles in the `app/AppKernel.php` file of your project:

```php
<?php
// app/AppKernel.php

// ...
class AppKernel extends Kernel
{
    public function registerBundles()
{
    $bundles = array(
    // ...

    new Puzzle\Admin\NewsletterBundle\PuzzleAdminNewsletterBundle(),
                    );

 // ...
}

 // ...
}
```

### Step 3: Register the Routes

Load the bundle's routing definition in the application (usually in the `app/config/routing.yml` file):

# app/config/routing.yml
```yaml
puzzle_admin:
        resource: "@PuzzleAdminNewsletterBundle/Resources/config/routing.yml"
```

### Step 4: Configure Dependency Injection

Then, enable management bundle via admin modules interface by adding it to the list of registered bundles in the `app/config/config.yml` file of your project under:

```yaml
# Puzzle Client Newsletter
puzzle_admin_newsletter:
    title: newsletter.title
    description: newsletter.description
    icon: newsletter.icon
    roles:
        newsletter:
            label: 'ROLE_NEWSLETTER'
            description: newsletter.role.default
```

### Step 5: Configure navigation menu

Then, enable management bundle via admin modules interface by adding it to the list of registered bundles in the `app/config/config.yml` file of your project under:

```yaml
# Client Admin
puzzle_admin:
    ...
    navigation:
    	nodes:
    		newsletter:
                label: 'newsletter.base'
                translation_domain: 'admin'
                attr:
                    class: 'icon-envelop3'
                parent: ~
                user_roles: ['ROLE_NEWSLETTER', 'ROLE_ADMIN']
                tooltip: 'newsletter.tooltip'
            newsletter_subscriber:
                label: 'newsletter.subscriber.base'
                translation_domain: 'admin'
                path: 'admin_newsletter_subscriber_list'
                sub_paths: ['admin_newsletter_subscriber_create', 'admin_newsletter_subscriber_update', 'admin_newsletter_subscriber_show']
                parent: newsletter
                user_roles: ['ROLE_NEWSLETTER', 'ROLE_ADMIN']
                tooltip: 'newsletter.tooltip'
            newsletter_group:
                label: 'newsletter.group.base'
                translation_domain: 'admin'
                path: 'admin_newsletter_group_list'
                sub_paths: ['admin_newsletter_group_create', 'admin_newsletter_group_update', 'admin_newsletter_group_show']
                parent: newsletter
                user_roles: ['ROLE_NEWSLETTER', 'ROLE_ADMIN']
                tooltip: 'newsletter.group.tooltip'
            newsletter_template:
                label: 'newsletter.template.base'
                translation_domain: 'admin'
                path: 'admin_newsletter_template_list'
                sub_paths: ['admin_newsletter_template_create', 'admin_newsletter_template_update', 'admin_newsletter_template_show']
                parent: newsletter
                user_roles: ['ROLE_NEWSLETTER', 'ROLE_ADMIN']
                tooltip: 'newsletter.template.tooltip'
```