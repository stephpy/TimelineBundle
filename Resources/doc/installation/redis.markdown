Redis Driver
==========

At this moment, this driver works with [SncRedisBundle](https://github.com/snc/SncRedisBundle), please install it if you want to use this driver.

/!\ WARNING /!\

Some features are not implemented on redis:

- subjectActions
- delivery wait

# 1) Define driver section on configuration

```yml
#config.yml
spy_timeline:
    drivers:
        redis:
            client:           ~ # snc_redis.default
            pipeline:         true
            prefix:           vlr_timeline
            classes:
                action:           'Spy\Timeline\Model\Action'
                component:        'Spy\Timeline\Model\Component'
                action_component: 'Spy\Timeline\Model\ActionComponent'
```

That's all

[index](https://github.com/stephpy/TimelineBundle/blob/master/Resources/doc/index.markdown)
