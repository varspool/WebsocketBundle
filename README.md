<!-- vim: set ft=markdown tw=79 sw=4 ts=4 et : -->
# VarspoolWebsocketBundle

Alpha stability. Provides websocket services, including an in-built server, 
multiplexing, semantic configuration.

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
your configuration, and you **must** define at least one to get started:

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

### Multiplexing

One thing I would recommend is multiplexing your javascript components'
connections. The SockJS way of doing that is [pretty
elegant](http://www.rabbitmq.com/blog/2012/02/23/how-to-compose-apps-using-websockets/),
and is supported by an application shipped along with this bundle.

The default configuration for this bundle (in
Varspool/WebsocketBundle/Resources/config/services.xml) defines a server-side multiplex
application, served at `ws://{host}:{port}/multiplex`. This application
implements the multiplex protocol that the [SockJS websocket-multiplex front-end
library](https://github.com/sockjs/websocket-multiplex) uses. They even have a
handy CDN:

```html
<script src="http://cdn.sockjs.org/websocket-multiplex-0.1.js"></script>
```

This library provides a `WebSocketMultiplex` object. You can feed it any object
compatible with a native `WebSocket`. So, to start with you can feed it a
native WebSocket, and later on, when you decide to install a SockJS server (or
one is implemented in PHP) you can feed it a SockJS object. So, like this:

```javascript
var url         = 'ws://example.com:8000/multiplex';

var socket;
if (window.MozWebSocket) {
    socket = new MozWebSocket(url);
} else if (window.WebSocket) {
    socket = new WebSocket(url);
} else {
    throw "No websocket support detected"
}

socket.binaryType = 'blob';

var real_socket = new WebSocket(url);
var multiplexer = new WebSocketMultiplex(real_socket);

var foo  = multiplexer.channel('bar');
// foo.send(), events: open, close, error, message

var logs = mutliplexer.channel('log_server');
// logs.send(), events: open, close, error, message

```

On the server side, you need only implement `Varspool\WebsocketBundle\Multiplex\Listener`
to be able to listen to events on a channel. Then just tag your service with
`varspool_websocket.multiplex_listener` and the topic you want to listen to:

```xml
<tag name="varspool_websocket.multiplex_listener" topic="chat" />
```

Even better: go the whole hog and extend 
`Varspool\WebsocketBundle\Services\MultiplexService`: no greater number of 
methods to implement in your service sub-class, and then you can use the 
`varspool_websocket.multiplex_service` parent tag:

```yaml
test.websocket_auth:
    class:  Application\ExampleBundle\Services\AuthService
    parent: varspool_websocket.multiplex_service
    tags:
        -
            name:  varspool_websocket.multiplex_listener
            topic: auth
        -
            name:  varspool_websocket.multiplex_listener
            topic: login
```

In this example, the AuthService will listen for messages on the `auth` and
`login` multiplex topics. It has `$this->multiplex` available to get the 
original multiplex application. And any time a message is recieved on the server 
this method is called:

```php
/**
 * @param Channel $channel   The channel is an object that holds all the active
 *          client connections to a given topic, and all the server-side 
 *          subscribers. You can ->send($message) to the channel to broadcast
 *          it to all the subscribed client connections. ->getTopic() identifies
 *          the topic the message was received on.
 *
 * @param string $message    The received message, as a string
 *
 * @param Connection $client The client connection the message was received 
 *          from. You can ->send($string) to the client, but it is a raw Websocket
 *          connection, so if you want to send a multiplexed message to a single
 *          client, you'll probably use 
 *          `Varspool\WebsocketBundle\Multiplex\Protocol::toString($type, $topic, $payload)`
 *          and the Protocol::TYPE_MESSAGE constant.
 */
public function onMessage(Channel $channel, $message, Connection $client = null);
```