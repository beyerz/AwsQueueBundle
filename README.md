# AWS Queue Bundle

## About
AWS Queue Bundle for Symfony2

The purpose of this bundle is to provide an easy integration between symfony services and AWS services.
Queues are defined by **Producers** and **Consumers**

By using this bundle, together with aws bundle, the **Consumer** and **Producer** services that you define will automatically setup the AWS services on demand. This eases the requirement of setting up your queue and notification services through the AWS platform.

# Installation

### Composer (Recommended)
```bash
    composer require beyerz/aws-queue-bundle
```
### Application Kernel

Add BeyerzAWSQueueBundle to the `registerBundles()` method of your application kernel:
```php
public function registerBundles()
{
    return array(
        new Beyerz\AWSQueueBundle\BeyerzAWSQueueBundle(),
    );
}
```

# Usage
## Config
config.yml
```yaml
beyerz_aws_queue:
  region: #Your AWS region that you want to use
  account: #Your AWS Account ID
  channel_prefix: "%kernel.environment%" #used to separate dev/stage/prod (mainly to make development eaiser)
  run_local: false #This option should allow you to run remotely using aws, or locally using no-queue or gearman
```

## Important config note:
Due to the way that Aws has defined the working tree, the aws-queue-bundle cannot automatically append your configs.<br>
The result of this is that you will need to define the aws configuration yourself.

## Documentation
### Creating a Producer

Creating a producer that can be used in your system is really simple, here is some sample code to get you started
You can also look at the demo folder in this bundle
_AcmeBundle/Producers/MyAwesomeProducer.php_
```php
<?php

namespace Acme\AcmeBundle\Producers;


use Beyerz\AWSQueueBundle\Interfaces\ProducerInterface;
use Beyerz\AWSQueueBundle\Producer\ProducerTrait;

class MyAwesomeProducer implements ProducerInterface
{
    use ProducerTrait;

    public function publish($message)
    {
        $this->producer->publish($message);
    }
}

```
Producers require some setup which is handled by this bundle and you dont really have to worry too much about it.
We have provided both and interface and trait for producers and as long you have included them, the setup should go smoothly.

Next, we need to define the producer as a service so that we can tag it and make the AwsQueueBundle aware that it exists :)

_AcmeBundle/Resources/config/services.yml_
```YAML
acme.my_awesome_producer:
    class: Acme\AcmeBundle\Producers\MyAwesomeProducer
    tags:
      - {name: "beyerz_aws_queue.producer", channel: "demo"}
```

You can now access your producer as you normally would through symfony container
```PHP
<?php
    $myAwesomeProducer = $this->container->get('acme.my_awesome_producer');
    $myMessage = "My Awesome Message";
    $myAwesomeProducer->publish($message);
```
After running this producer, you can go to SQS service in your amazon account and see messages waiting in your queue.
Pretty Awesome!!!

### Creating a Consumer
