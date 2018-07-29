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

Producers push to amazon sns service
sns then pushes to amazon sqs
Consumers listen to amazon sqs and consume when new queue items are detected