<!-- vim: set ft=markdown tw=79 sw=4 ts=4 et : -->
# VarspoolWebsocketBundle

Provides websocket services, including an in-built server.
## Installation

VarspoolWebsocketBundle depends on:

* lemmingzshadow/php-websocket
** This is forked from nicokaiser/php-websocket, which seems abandoned.
* symfony/Console

### deps file

If you're using bin/vendors to configure your dependencies, add the following
lines to your `deps` file:

```ini
[websocket]
    git=git://github.com/lemmingzshadow/php-websocket.git
    version=origin/master

[VarspoolWebsocketBundle]
    git=git://github.com/dominics/VarspoolWebsocketBundle.git
    target=/bundles/Varspool/WebsocketBundle
    version=origin/master
```

Or, fork to your own repository first so you can send in pull requests and 
improve upstream :+1:. Once you've done this you can use `bin/vendors` to obtain
the bundles:

```
$ bin/vendors update

[...]
  > Installing/Updating websocket
  > Installing/Updating VarspoolWebsocketBundle
```

### app/autoload.php

Register the Varspool and Websocket namespaces in your autoloader:

```php
# app/autoload.php
$loader->registerNamespaces(array(
    // [...]
    'Varspool'   => __DIR__.'/../vendor/bundles',
    'WebSocket'  => __DIR__.'/../vendor/websocket/server/lib' // NB: Capital S
));
```

### app/AppKernel.php

Register the `VarspoolWebsocketBundle`:

```php
# app/AppKernel.php
public function registerBundles()
{
    $bundles = array(
        //...
        new Varspool\WebsocketBundle\VarspoolWebsocketBundle(),
    );
}
```

## Usage

### Starting a Server

The bundle provides a `websocket:listen` console command, accessible through
`app/console`:

```
Usage:
 websocket:listen [server_name]

Arguments:
 server_name      The server name (from your varspool_websocket configuration) (default: default)
```

This command can be used to start a websocket server. Servers are defined in 
your configuration, and you'll have to at least define one to get started:

```yaml
varspool_websocket:
    servers:
        default:
            host: 192.168.1.103     # default: localhost, interface to listen on
            #port: 8000
            #max_clients: 100
            #max_connections_per_ip: 5
            #max_requests_per_minute: 50
            #check_origin: true
            allow_origin:           # origins allowed to connect to this server
                - "example.org"
                - "example.com"
```

Once you've configured a server, run the websocket:listen command. When it runs,
the server will start up and run applications you've defined in your configuration.
Specifically, it looks for services tagged as `varspool_websocket.application`.
So, to run an application, export a service with that tag:

```yaml
  websocket_example:
    class: Application\ExampleBundle\Application\ExampleApplication
    tags:
      - { name: varspool_websocket.application }
```

I suggest you make your application classes extend
 `Varspool\WebsocketBundle\Application\Application`, but it's mostly optional.
If you like, you can just implement the NamedApplication interface.

For a simple example application, see `Application\EchoApplication`. Here's 
what that the command looks like when you run it:

```
$ app/console websocket:listen default
info: Listening on 192.168.1.103:8080 with ssl off
info: Server created
info: Registering application: test (Application\TestBundle\Application\TestApplication)
info: Registering application: auth (Application\TestBundle\Application\AuthApplication)
info: Registering application: multiplex (Varspool\WebsocketBundle\Application\MultiplexApplication)
info: Registering application: echo (Varspool\WebsocketBundle\Application\EchoApplication)
```

### Connecting from Javascript

Of course, you'll need a browser that supports websockets. 

As for Javascript libraries, they're mostly up to you. But unless you're 
already using Coffeescript, you might find the ones shipped along with 
php-websocket a pain to install.



/////////// UP TO HERE
<!--

### app/config.yml

Next, configure the default Markdown renderer for the `kwattro_markdown` service,
so that it'll stop complaining.

```yaml
kwattro_markdown:
    renderer:     xhtml
```

You can optionally configure where to find the `pygmentize` script. The default
is `/usr/bin/pygmentize`:

```yaml
varspool_websocket:
    bin:     /usr/local/bin/pygmentize
```

## Usage

### Services

#### kwattro_markdown

KwattroMarkdownBundle usually provides the `kwattro_markdown` service. This 
won't change when you set up VarspoolWebsocketBundle: the service will continue
to provide a Markdown rendering without syntax highlighting. This service is
usually a `Kwattro\MarkdownBundle\Markdown\KwattroMarkdown` object.

```php
$xhtml = $this->get('kwattro_markdown')->render($markdown_source);
```

#### varspool_markdown

Once you've installed VarspoolWebsocketBundle, you'll have a second service 
available: `vaspool_markdown`. This service will extend
`Kwattro\MarkdownBundle\Markdown\KwattroMarkdown`, so you should just be able
to swap it in as a replacement quite easily. It'll colorize fenced code blocks
in the markdown. This service is usually a 
`Varspool\WebsocketBundle\Markdown\KwattroMarkdown` object.

```php
$colorized_xhtml = $this->get('varspool_markdown')->render($markdown_source);
```

#### varspool_websocket

This service is the Sundown renderer instance responsible for coloring the 
output. It's usually an instance of `Varspool\WebsocketBundle\Sundown\Render\ColorXHTML`.

### Stylesheets

The Websocket renderer marks up parts of the output with `div` tags and classes.
You'll then need to assign stlying to these tags.

#### SCSS/Compass

If you're already using Compass or SASS, there's an example Websocket stylesheet
in Resources/public/css/_websocket.scss. The default implementation uses the 
[Solarized](http://ethanschoonover.com/solarized) color scheme. You should be
able to @import this stylesheet from one of your own.

#### Dynamic Styles

Websocket can provide one of several stylesheets to automatically color the 
output. A controller is provided that will output styles by calling
`pygmentize -S <style>`. To use the controller, reference it from your routing:

```yaml
# app/config/routing.yml
varspool_websocket:
  resource: '@VarspoolWebsocketBundle/Controller/WebsocketController.php'
  type: annotation
``` 

Then include a CSS file in your page via the URL `/websocket/<websocket_formatter>/<websocket_style>.css`.
(e.g. /websocket/html/friendly.css).

Alternatively, you can get the styles as a string from the varspool_websocket service:

```php
$websocket_formatter = $this->container->get('varspool_websocket');
$styles = $websocket_formatter->getStyles('friendly');
```

-->