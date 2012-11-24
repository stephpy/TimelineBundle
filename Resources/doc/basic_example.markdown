# Basic example

This example explain how to have a simple Timeline with `GLOBAL` context.

##Context:

`Chuck norris` just control `the world`. All world redisent have to be informed about that !!!!!!!!!


## First step

Add this to your controller:

```php
<?php
use Acme\YourBundle\Entity\TimelineAction
// or other DbDriver TimelineAction.

//.....

public functio myAction()
{
    //......
    $entry = TimelineAction::create($chuckNorrisObject, 'control', 'The world');

    $this->get('spy_timeline.manager')->push($entry);
}
```

But actually there is no spread for this action, the timeline action will be stored on your db_driver but nobody will be informed about this.

## Second step

Define your Spread.

Define the service `Acme\MyBundle\Resource\config\services.xml`:
Look at [documentation](http://symfony.com/doc/current/book/service_container.html) to know how to do.

```xml
<service id="my_spread" class="Acme\MyBundle\Spread\MySpread">
    <tag name="spy_timeline.spread"/>
</service>
```

Now, create the class `Acme\MyBundle\Spread\MySpread`

```php
<?php

namespace Acme\MyBundle\Spread;

use Spy\TimelineBundle\Spread\SpreadInterface;
use Spy\TimelineBundle\Spread\Entry\EntryCollection;
use Spy\TimelineBundle\Spread\Entry\Entry;
use Spy\TimelineBundle\Model\TimelineAction;

class MySpread implements SpreadInterface
{
    public function supports(TimelineAction $timelineAction)
    {
        // here you define what actions you want to support, you have to return a boolean.
        if ($timelineAction->getSubject()->getName() == "ChuckNorris") {
            return true;
        } else {
            return false;
        }
    }

    public function process(TimelineAction $timelineAction, EntryCollection $coll)
    {
        // adding steven seagal to be informed
        // 1337 is the id of user steven seagal
        // here GLOBAL is the context

        $coll->set('GLOBAL', Entry::create('\User', 1337));

        // get all other users
        $users = MyBestClass::MyBestMethodToGetNerds();

        foreach ($users as $user) {
            $coll->set('GLOBAL', Entry::create(get_class($user), $user->getId()));
        }
    }
}
```

## Third step

It's ok, now you can get timeline actions for each users

In your controller:

```php
<?php
public function myAction()
{
    // Get the timeline (an array of TimelineAction) of Steven Seagal
    $results = $this->get('spy_timeline.manager')
        ->getWall('\User', 1337, 'GLOBAL');

    // how many entries are stored in redis.
    $countEntries = $this->get('spy_timeline.manager')
        ->countWallEntries('\User', 1337, 'GLOBAL');

    // this method works with annotations.
    return array('coll' => $results);
}

```

In your template .twig:

```twig
{% for timeline in coll %}
    {{ timeline_render(timeline) }}
    {# i18n ? #}
    {{ i18n_timeline_render(timeline, 'en') }}

{% endfor %}
```

Look at [renderer](https://github.com/stephpy/TimelineBundle/blob/master/Resources/doc/renderer.markdown) to see how to define a path to store verbs.

If you have any question, feel free to ask me
