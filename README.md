# AWS Queue Bundle 
[![PHP from Packagist](https://img.shields.io/packagist/php-v/beyerz/aws-queue-bundle.svg?style=flat-square&colorB=8892BF)](https://www.php.net/)
[![Latest Stable Version](https://img.shields.io/packagist/v/beyerz/aws-queue-bundle.svg?colorB=orange&style=flat-square)](https://packagist.org/packages/beyerz/aws-queue-bundle)
[![Downloads from Packagist](https://img.shields.io/packagist/dt/beyerz/aws-queue-bundle.svg?style=flat-square&colorB=red)](https://packagist.org/packages/beyerz/aws-queue-bundle)
[![Build Status](https://travis-ci.org/beyerz/AwsQueueBundle.svg?branch=master)](https://travis-ci.org/beyerz/AwsQueueBundle)
[![codecov](https://codecov.io/gh/beyerz/AwsQueueBundle/branch/master/graph/badge.svg)](https://codecov.io/gh/beyerz/AwsQueueBundle)

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
parameters:
    acme.my_awesome_producer.class: Acme\AcmeBundle\Producers\MyAwesomeProducer
    
services:
    acme.my_awesome_producer:
        class: "%acme.my_awesome_producer.class%"
        tags:
            - {name: "beyerz_aws_queue.producer", channel: "awesome_producer"}
```

You can now access your producer as you normally would through symfony container
```PHP
<?php
    $myAwesomeProducer = $this->container->get('acme.my_awesome_producer');
    $myMessage = "My Awesome Message";
    $myAwesomeProducer->publish($message);
```
After running this producer, you can go to SNS service in your amazon account and see a new topic has been created for you.
You will not yet have queues in your SQS as we have not yet created any consumers

### Creating a Consumer
Consumer are a little more complex than producers, but the basic concept to understand is that your consumer class receives the message from the queue for you to process.

_AcmeBundle/Consumers/MyAwesomeConsumer.php_
```php
<?php

namespace Acme\AcmeBundle\Consumers;


use Beyerz\AWSQueueBundle\Interfaces\ConsumerInterface;

class MyAwesomeConsumer implements ConsumerInterface
{

    public function consume($message)
    {
        //do something awesome with your message
    }
}

```
Consumers require some setup which is handled by this bundle and you dont really have to worry too much about it.
We have provided both an interface and as long you have implemented it, the setup should go smoothly.
Your consumer class gets "wrapped" in a special consumer service. This wrapper connects to the queue and passes the message to your consume function. This wrapper loads your consumer using service container, this means that you can create your service as you normally would, so you can even add dependecies to a constructor!

Next, we need to define the consumer as a service so that we can tag it and make the AwsQueueBundle aware that it exists :)
There are two tags that we are going to use.
The first tag tells the bundle that this is a consumer and the defines the name of the SQS queue using the channel key
The second tag tells the bundle which producer this consumer wants to subscribe to.
If you want to subscribe this queue to more than one producer, just duplicate the tag and define the producer name accordingly

_AcmeBundle/Resources/config/services.yml_
```YAML
parameters:
    acme.my_awesome_consumer.class: Acme\AcmeBundle\Consumers\MyAwesomeConsumer
    
services:
    acme.my_awesome_consumer:
        class: "%acme.my_awesome_consumer.class%"
        tags:
            - {name: "beyerz_aws_queue.consumer", channel: "awesome_consumer"}
            - {name: "beyerz_aws_queue.subscriber", producer: "awesome_producer"}
```

Now you can run your producer using cli
```bash
    php app/console beyerz:consumer awesome_consumer
```

Your consumer will keep running unless you specify a max message limit or something crashes.
Its probably best to start your consumers using a task management system such as supervisor or even jenkins.
If you have processes that you prefer to run at specific times and not continuosly you can also use crontab
